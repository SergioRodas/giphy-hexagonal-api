<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Domain\Shared\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    #[Test]
    public function it_normalizes_to_lowercase_and_trims(): void
    {
        $email = new Email('  Demo@Example.COM ');

        $this->assertSame('demo@example.com', $email->value());
    }

    #[Test]
    public function two_emails_with_different_casing_are_equal(): void
    {
        $this->assertTrue((new Email('a@b.com'))->equals(new Email('A@B.com')));
    }

    #[Test]
    public function it_rejects_an_invalid_address(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('not-an-email');
    }

    #[Test]
    public function it_rejects_an_empty_address(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('   ');
    }
}
