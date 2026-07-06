<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Domain\Gif\ValueObject\GifId;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GifIdTest extends TestCase
{
    #[Test]
    public function it_accepts_an_alphanumeric_identifier(): void
    {
        $id = new GifId('Ev477g37MJORyOWfdG');

        $this->assertSame('Ev477g37MJORyOWfdG', $id->value());
    }

    #[Test]
    public function it_rejects_an_empty_identifier(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GifId('   ');
    }

    #[Test]
    public function it_rejects_non_alphanumeric_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GifId('abc-123/../etc');
    }
}
