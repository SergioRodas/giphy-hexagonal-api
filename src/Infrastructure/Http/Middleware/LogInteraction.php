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
     * Request fields that must never be persisted in clear text.
     */
    private const array REDACTED_REQUEST = ['password', 'password_confirmation'];

    /**
     * Response fields (issued credentials) that must never be persisted in clear text.
     */
    private const array REDACTED_RESPONSE = ['access_token', 'refresh_token', 'token'];

    public function __construct(private readonly RequestLogRepository $logs) {}

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        // Registered globally so unmatched /api/* requests (404/405) are audited
        // too; only API interactions are recorded.
        if (! $request->is('api/*')) {
            return;
        }

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
        return $this->redact($request->all(), self::REDACTED_REQUEST);
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

        if (! is_array($decoded)) {
            return ['raw' => Str::limit($content, 2000)];
        }

        // Never persist issued credentials (e.g. the login access token) in clear text.
        return $this->redact($decoded, self::REDACTED_RESPONSE);
    }

    /**
     * Masks sensitive fields at any nesting depth, so future endpoints with
     * nested payloads cannot accidentally persist a secret.
     *
     * @param  array<array-key, mixed>  $data
     * @param  list<string>  $fields
     * @return array<array-key, mixed>
     */
    private function redact(array $data, array $fields): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $fields, true)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = $this->redact($value, $fields);
            }
        }

        return $data;
    }
}
