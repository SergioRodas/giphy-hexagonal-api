<?php

declare(strict_types=1);

namespace Tests\Support;

use Domain\Gif\Entity\Gif;
use Domain\Gif\Exception\GifNotFound;
use Domain\Gif\GifSearchResult;
use Domain\Gif\Repository\GifRepository;
use Domain\Gif\ValueObject\GifId;
use Domain\Gif\ValueObject\SearchCriteria;

/**
 * Configurable in-memory GifRepository double for use-case unit tests.
 */
final class FakeGifRepository implements GifRepository
{
    public ?SearchCriteria $lastCriteria = null;

    /** @var array<string, Gif> */
    private array $gifs = [];

    public function __construct(private ?GifSearchResult $searchResult = null)
    {
    }

    public function withGif(Gif $gif): self
    {
        $this->gifs[$gif->id()->value()] = $gif;

        return $this;
    }

    public function search(SearchCriteria $criteria): GifSearchResult
    {
        $this->lastCriteria = $criteria;

        return $this->searchResult ?? new GifSearchResult([], new \Domain\Gif\ValueObject\Pagination(0, 0, 0));
    }

    public function findById(GifId $id): Gif
    {
        return $this->gifs[$id->value()] ?? throw GifNotFound::withId($id);
    }
}
