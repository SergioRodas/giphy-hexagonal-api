<?php

declare(strict_types=1);

namespace Infrastructure\Giphy;

use Domain\Gif\Entity\Gif;
use Domain\Gif\GifSearchResult;
use Domain\Gif\ValueObject\GifId;
use Domain\Gif\ValueObject\GifImage;
use Domain\Gif\ValueObject\Pagination;

/**
 * Translates raw GIPHY API payloads into the domain model, isolating the rest
 * of the application from the provider's response shape.
 */
final class GiphyGifMapper
{
    /**
     * @param  array<string, mixed>  $payload  full search response body
     */
    public function toSearchResult(array $payload): GifSearchResult
    {
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        $gifs = array_values(array_map(
            fn (array $gif): Gif => $this->toGif($gif),
            $data,
        ));

        $pagination = is_array($payload['pagination'] ?? null) ? $payload['pagination'] : [];

        return new GifSearchResult(
            $gifs,
            new Pagination(
                (int) ($pagination['total_count'] ?? count($gifs)),
                (int) ($pagination['count'] ?? count($gifs)),
                (int) ($pagination['offset'] ?? 0),
            ),
        );
    }

    /**
     * @param  array<string, mixed>  $data  a single GIF resource
     */
    public function toGif(array $data): Gif
    {
        $images = is_array($data['images'] ?? null) ? $data['images'] : [];

        $username = trim((string) ($data['username'] ?? ''));

        return new Gif(
            new GifId((string) $data['id']),
            (string) ($data['title'] ?? ''),
            (string) ($data['url'] ?? ''),
            (string) ($data['rating'] ?? ''),
            $username === '' ? null : $username,
            $this->toImage($images['original'] ?? []),
            $this->toPreviewImage($images),
        );
    }

    /**
     * @param  array<string, mixed>  $images
     */
    private function toPreviewImage(array $images): ?GifImage
    {
        foreach (['fixed_height_small', 'fixed_height', 'downsized', 'preview_gif'] as $key) {
            if (is_array($images[$key] ?? null) && ! empty($images[$key]['url'])) {
                return $this->toImage($images[$key]);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $image
     */
    private function toImage(array $image): GifImage
    {
        return new GifImage(
            (string) ($image['url'] ?? ''),
            (int) ($image['width'] ?? 0),
            (int) ($image['height'] ?? 0),
        );
    }
}
