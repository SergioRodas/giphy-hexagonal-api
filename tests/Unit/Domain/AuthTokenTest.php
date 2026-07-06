<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use DateTimeImmutable;
use Domain\Auth\ValueObject\AuthToken;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AuthTokenTest extends TestCase
{
    #[Test]
    public function it_computes_the_remaining_lifetime_in_seconds(): void
    {
        $token = new AuthToken('abc', new DateTimeImmutable('2026-01-01T00:30:00+00:00'));

        $secondsLeft = $token->expiresIn(new DateTimeImmutable('2026-01-01T00:00:00+00:00'));

        $this->assertSame(1800, $secondsLeft);
    }

    #[Test]
    public function the_remaining_lifetime_is_never_negative(): void
    {
        $token = new AuthToken('abc', new DateTimeImmutable('2026-01-01T00:00:00+00:00'));

        $secondsLeft = $token->expiresIn(new DateTimeImmutable('2026-01-01T01:00:00+00:00'));

        $this->assertSame(0, $secondsLeft);
    }

    #[Test]
    public function it_defaults_to_a_bearer_token(): void
    {
        $token = new AuthToken('abc', new DateTimeImmutable('2026-01-01T00:30:00+00:00'));

        $this->assertSame('Bearer', $token->tokenType());
    }

    #[Test]
    public function it_rejects_an_empty_access_token(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AuthToken('   ', new DateTimeImmutable('2026-01-01T00:30:00+00:00'));
    }
}
