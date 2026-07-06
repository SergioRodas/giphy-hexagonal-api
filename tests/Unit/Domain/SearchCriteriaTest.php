<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Domain\Gif\ValueObject\SearchCriteria;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SearchCriteriaTest extends TestCase
{
    #[Test]
    public function it_applies_defaults_for_missing_limit_and_offset(): void
    {
        $criteria = SearchCriteria::fromNullable('cats', null, null);

        $this->assertSame('cats', $criteria->query());
        $this->assertSame(SearchCriteria::DEFAULT_LIMIT, $criteria->limit());
        $this->assertSame(0, $criteria->offset());
    }

    #[Test]
    public function it_rejects_an_empty_query(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SearchCriteria('   ');
    }

    #[Test]
    public function it_rejects_a_limit_above_the_provider_maximum(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SearchCriteria('cats', SearchCriteria::MAX_LIMIT + 1);
    }

    #[Test]
    public function it_rejects_a_negative_offset(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SearchCriteria('cats', 10, -1);
    }
}
