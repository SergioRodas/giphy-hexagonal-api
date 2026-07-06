<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Application\Gif\Search\SearchGifsQuery;
use Application\Gif\Search\SearchGifsUseCase;
use Domain\Gif\Entity\Gif;
use Domain\Gif\GifSearchResult;
use Domain\Gif\ValueObject\GifId;
use Domain\Gif\ValueObject\GifImage;
use Domain\Gif\ValueObject\Pagination;
use Domain\Gif\ValueObject\SearchCriteria;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeGifRepository;

final class SearchGifsUseCaseTest extends TestCase
{
    #[Test]
    public function it_forwards_the_given_filters_to_the_repository(): void
    {
        $repository = new FakeGifRepository($this->aResultWithOneGif());
        $useCase = new SearchGifsUseCase($repository);

        $result = $useCase->execute(new SearchGifsQuery('cats', 5, 10));

        $this->assertCount(1, $result->gifs());
        $this->assertSame('cats', $repository->lastCriteria?->query());
        $this->assertSame(5, $repository->lastCriteria?->limit());
        $this->assertSame(10, $repository->lastCriteria?->offset());
    }

    #[Test]
    public function it_applies_default_limit_and_offset_when_omitted(): void
    {
        $repository = new FakeGifRepository($this->aResultWithOneGif());
        $useCase = new SearchGifsUseCase($repository);

        $useCase->execute(new SearchGifsQuery('cats'));

        $this->assertSame(SearchCriteria::DEFAULT_LIMIT, $repository->lastCriteria?->limit());
        $this->assertSame(0, $repository->lastCriteria?->offset());
    }

    private function aResultWithOneGif(): GifSearchResult
    {
        $gif = new Gif(
            new GifId('abc123'),
            'A cat',
            'https://giphy.com/gifs/abc123',
            'g',
            'someone',
            new GifImage('https://media.giphy.com/abc123.gif', 480, 270),
            null,
        );

        return new GifSearchResult([$gif], new Pagination(1, 1, 0));
    }
}
