<?php

declare(strict_types=1);

namespace Domain\Gif\ValueObject;

/**
 * Pagination metadata returned alongside a GIF search result.
 */
final readonly class Pagination
{
    public function __construct(
        private int $totalCount,
        private int $count,
        private int $offset,
    ) {
    }

    public function totalCount(): int
    {
        return $this->totalCount;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function offset(): int
    {
        return $this->offset;
    }
}
