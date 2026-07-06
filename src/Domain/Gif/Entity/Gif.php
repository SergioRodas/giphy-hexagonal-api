<?php

declare(strict_types=1);

namespace Domain\Gif\Entity;

use Domain\Gif\ValueObject\GifId;
use Domain\Gif\ValueObject\GifImage;

/**
 * A GIF as modelled by our domain (a curated subset of the GIPHY resource).
 */
final readonly class Gif
{
    public function __construct(
        private GifId $id,
        private string $title,
        private string $url,
        private string $rating,
        private ?string $username,
        private GifImage $original,
        private ?GifImage $preview,
    ) {
    }

    public function id(): GifId
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function rating(): string
    {
        return $this->rating;
    }

    public function username(): ?string
    {
        return $this->username;
    }

    public function original(): GifImage
    {
        return $this->original;
    }

    public function preview(): ?GifImage
    {
        return $this->preview;
    }
}
