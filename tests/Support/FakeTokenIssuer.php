<?php

declare(strict_types=1);

namespace Tests\Support;

use DateTimeImmutable;
use Domain\Auth\Contract\TokenIssuer;
use Domain\Auth\ValueObject\AuthToken;
use Domain\User\Entity\User;

/**
 * Test double that issues a deterministic access token and records the user it
 * was asked to issue a token for.
 */
final class FakeTokenIssuer implements TokenIssuer
{
    public ?User $issuedFor = null;

    public function __construct(private readonly string $token = 'fake-access-token') {}

    public function issueFor(User $user): AuthToken
    {
        $this->issuedFor = $user;

        return new AuthToken(
            $this->token,
            new DateTimeImmutable('2026-01-01T00:30:00+00:00'),
        );
    }
}
