<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Application\Gif\Show\GetGifByIdQuery;
use Application\Gif\Show\GetGifByIdUseCase;
use Domain\Gif\Entity\Gif;
use Domain\Gif\Exception\GifNotFound;
use Domain\Gif\ValueObject\GifId;
use Domain\Gif\ValueObject\GifImage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeGifRepository;

final class GetGifByIdUseCaseTest extends TestCase
{
    #[Test]
    public function it_returns_the_matching_gif(): void
    {
        $gif = new Gif(
            new GifId('abc123'),
            'A cat',
            'https://giphy.com/gifs/abc123',
            'g',
            null,
            new GifImage('https://media.giphy.com/abc123.gif', 480, 270),
            null,
        );

        $useCase = new GetGifByIdUseCase((new FakeGifRepository())->withGif($gif));

        $found = $useCase->execute(new GetGifByIdQuery('abc123'));

        $this->assertSame('abc123', $found->id()->value());
        $this->assertSame('A cat', $found->title());
    }

    #[Test]
    public function it_propagates_a_not_found_error_mapped_to_http_404(): void
    {
        $useCase = new GetGifByIdUseCase(new FakeGifRepository());

        try {
            $useCase->execute(new GetGifByIdQuery('missing'));
            $this->fail('Expected GifNotFound.');
        } catch (GifNotFound $e) {
            $this->assertSame(404, $e->statusCode());
        }
    }
}
