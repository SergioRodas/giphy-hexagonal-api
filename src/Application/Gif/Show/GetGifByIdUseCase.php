<?php

declare(strict_types=1);

namespace Application\Gif\Show;

use Domain\Gif\Entity\Gif;
use Domain\Gif\Repository\GifRepository;
use Domain\Gif\ValueObject\GifId;

/**
 * Retrieves the information of a single GIF by its identifier.
 */
final readonly class GetGifByIdUseCase
{
    public function __construct(private GifRepository $gifs) {}

    public function execute(GetGifByIdQuery $query): Gif
    {
        return $this->gifs->findById(new GifId($query->id));
    }
}
