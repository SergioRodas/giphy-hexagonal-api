<?php

declare(strict_types=1);

namespace Domain\Gif;

use Domain\Gif\Entity\Gif;
use Domain\Gif\ValueObject\Pagination;

/**
 * The outcome of a GIF search: the matching GIFs plus pagination metadata.
 */
final readonly class GifSearchResult
{
    /**
     * @param  list<Gif>  $gifs
     */
    public function __construct(
        private array $gifs,
        private Pagination $pagination,
    ) {}

    /**
     * @return list<Gif>
     */
    public function gifs(): array
    {
        return $this->gifs;
    }

    public function pagination(): Pagination
    {
        return $this->pagination;
    }
}
