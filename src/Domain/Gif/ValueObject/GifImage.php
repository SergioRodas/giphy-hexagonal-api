<?php

declare(strict_types=1);

namespace Domain\Gif\ValueObject;

/**
 * A single rendition of a GIF (a concrete image URL and its dimensions).
 */
final readonly class GifImage
{
    public function __construct(
        private string $url,
        private int $width,
        private int $height,
    ) {
    }

    public function url(): string
    {
        return $this->url;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }
}
