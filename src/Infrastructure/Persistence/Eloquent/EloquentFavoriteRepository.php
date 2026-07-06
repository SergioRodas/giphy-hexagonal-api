<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent;

use DateTimeImmutable;
use Domain\Favorite\Entity\Favorite;
use Domain\Favorite\Repository\FavoriteRepository;
use Domain\Favorite\ValueObject\FavoriteId;
use Domain\Gif\ValueObject\GifId;
use Domain\User\ValueObject\UserId;
use Infrastructure\Persistence\Eloquent\Models\FavoriteModel;

final class EloquentFavoriteRepository implements FavoriteRepository
{
    public function save(Favorite $favorite): Favorite
    {
        $model = FavoriteModel::query()->create([
            'user_id' => $favorite->userId()->value(),
            'gif_id' => $favorite->gifId()->value(),
            'alias' => $favorite->alias()->value(),
        ]);

        return Favorite::reconstitute(
            new FavoriteId($model->id),
            $favorite->userId(),
            $favorite->gifId(),
            $favorite->alias(),
            DateTimeImmutable::createFromInterface($model->created_at),
        );
    }

    public function existsForUserAndGif(UserId $userId, GifId $gifId): bool
    {
        return FavoriteModel::query()
            ->where('user_id', $userId->value())
            ->where('gif_id', $gifId->value())
            ->exists();
    }
}
