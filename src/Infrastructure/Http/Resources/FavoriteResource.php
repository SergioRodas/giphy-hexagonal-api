<?php

declare(strict_types=1);

namespace Infrastructure\Http\Resources;

use DateTimeInterface;
use Domain\Favorite\Entity\Favorite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Favorite $resource
 */
class FavoriteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $favorite = $this->resource;

        return [
            'id' => $favorite->id()->value(),
            'user_id' => $favorite->userId()->value(),
            'gif_id' => $favorite->gifId()->value(),
            'alias' => $favorite->alias()->value(),
            'created_at' => $favorite->createdAt()?->format(DateTimeInterface::ATOM),
        ];
    }
}
