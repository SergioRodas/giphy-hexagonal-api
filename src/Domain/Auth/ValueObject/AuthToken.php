<?php

declare(strict_types=1);

namespace Domain\Auth\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * An issued OAuth2 access token together with its lifetime.
 */
final readonly class AuthToken
{
    public function __construct(
        private string $accessToken,
        private DateTimeImmutable $expiresAt,
        private string $tokenType = 'Bearer',
    ) {
        if (trim($accessToken) === '') {
            throw new InvalidArgumentException('Access token cannot be empty.');
        }
    }

    public function accessToken(): string
    {
        return $this->accessToken;
    }

    public function tokenType(): string
    {
        return $this->tokenType;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * Seconds remaining until expiration, relative to the given "now".
     */
    public function expiresIn(DateTimeImmutable $now): int
    {
        return max(0, $this->expiresAt->getTimestamp() - $now->getTimestamp());
    }
}
