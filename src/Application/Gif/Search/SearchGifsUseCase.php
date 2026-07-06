<?php

declare(strict_types=1);

namespace Application\Gif\Search;

use Domain\Gif\GifSearchResult;
use Domain\Gif\Repository\GifRepository;
use Domain\Gif\ValueObject\SearchCriteria;

/**
 * Searches GIFs by phrase/term against the external provider.
 */
final readonly class SearchGifsUseCase
{
    public function __construct(private GifRepository $gifs) {}

    public function execute(SearchGifsQuery $query): GifSearchResult
    {
        $criteria = SearchCriteria::fromNullable($query->query, $query->limit, $query->offset);

        return $this->gifs->search($criteria);
    }
}
