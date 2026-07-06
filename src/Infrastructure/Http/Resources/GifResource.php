<?php

declare(strict_types=1);

namespace Infrastructure\Http\Resources;

use Domain\Gif\Entity\Gif;
use Domain\Gif\ValueObject\GifImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Gif $resource
 */
class GifResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $gif = $this->resource;

        return [
            'id' => $gif->id()->value(),
            'title' => $gif->title(),
            'url' => $gif->url(),
            'rating' => $gif->rating(),
            'username' => $gif->username(),
            'images' => [
                'original' => $this->image($gif->original()),
                'preview' => $gif->preview() !== null ? $this->image($gif->preview()) : null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function image(GifImage $image): array
    {
        return [
            'url' => $image->url(),
            'width' => $image->width(),
            'height' => $image->height(),
        ];
    }
}
