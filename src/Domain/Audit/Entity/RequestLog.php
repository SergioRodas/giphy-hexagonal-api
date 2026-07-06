<?php

declare(strict_types=1);

namespace Domain\Audit\Entity;

use Domain\User\ValueObject\UserId;

/**
 * An audit record of a single interaction with the API, capturing the six
 * pieces of information required by the challenge:
 *   user, service, request body, response status, response body and origin IP.
 */
final readonly class RequestLog
{
    /**
     * @param array<string, mixed> $requestBody
     * @param array<string, mixed>|null $responseBody
     */
    public function __construct(
        private ?UserId $userId,
        private string $service,
        private string $method,
        private string $path,
        private array $requestBody,
        private int $statusCode,
        private ?array $responseBody,
        private string $ipAddress,
    ) {
    }

    public function userId(): ?UserId
    {
        return $this->userId;
    }

    public function service(): string
    {
        return $this->service;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return array<string, mixed>
     */
    public function requestBody(): array
    {
        return $this->requestBody;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function responseBody(): ?array
    {
        return $this->responseBody;
    }

    public function ipAddress(): string
    {
        return $this->ipAddress;
    }
}
