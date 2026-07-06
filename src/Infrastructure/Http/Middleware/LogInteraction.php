<?php

declare(strict_types=1);

namespace Infrastructure\Http\Middleware;

use Closure;
use Domain\Audit\Entity\RequestLog;
use Domain\Audit\Repository\RequestLogRepository;
use Domain\User\ValueObject\UserId;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Persists every interaction with the API, capturing the six data points
 * required by the challenge: user, service, request body, response status,
 * response body and origin IP.
 *
 * The record is written on terminate() so it also captures the final response
 * produced by the exception handler (e.g. 401/404/409/422/502).
 */
final class LogInteraction
{
    /**
     * Fields that must never be persisted in clear text.
     */
    private const array REDACTED = ['password', 'password_confirmation'];

    public function __construct(private readonly RequestLogRepository $logs)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            $this->logs->save(new RequestLog(
                $this->resolveUserId($request),
                $this->resolveService($request),
                $request->getMethod(),
                $request->path(),
                $this->requestBody($request),
                $response->getStatusCode(),
                $this->responseBody($response),
                (string) $request->ip(),
            ));
        } catch (Throwable $exception) {
            // Auditing must never break the actual request.
            report($exception);
        }
    }

    private function resolveUserId(Request $request): ?UserId
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        $identifier = $user->getAuthIdentifier();

        return is_numeric($identifier) ? new UserId((int) $identifier) : null;
    }

    private function resolveService(Request $request): string
    {
        return $request->route()?->getName() ?? $request->path();
    }

    /**
     * @return array<string, mixed>
     */
    private function requestBody(Request $request): array
    {
        $body = $request->all();

        foreach (self::REDACTED as $field) {
            if (array_key_exists($field, $body)) {
                $body[$field] = '[REDACTED]';
            }
        }

        return $body;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function responseBody(Response $response): ?array
    {
        $content = $response->getContent();

        if ($content === false || $content === '') {
            return null;
        }

        $decoded = json_decode($content, true);

        return is_array($decoded)
            ? $decoded
            : ['raw' => Str::limit($content, 2000)];
    }
}
