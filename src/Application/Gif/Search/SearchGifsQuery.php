<?php

declare(strict_types=1);

namespace Application\Gif\Search;

/**
 * Input DTO for the Search GIFs use case.
 */
final readonly class SearchGifsQuery
{
    public function __construct(
        public string $query,
        public ?int $limit = null,
        public ?int $offset = null,
    ) {
    }
}
