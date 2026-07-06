<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Domain\Favorite\ValueObject\Alias;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AliasTest extends TestCase
{
    #[Test]
    public function it_trims_surrounding_whitespace(): void
    {
        $this->assertSame('My cat', (new Alias('  My cat  '))->value());
    }

    #[Test]
    public function it_rejects_an_empty_alias(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Alias('   ');
    }

    #[Test]
    public function it_rejects_an_alias_longer_than_the_maximum(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Alias(str_repeat('a', Alias::MAX_LENGTH + 1));
    }
}
