<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent;

use DateTimeImmutable;
use Domain\Favorite\Entity\Favorite;
use Domain\Favorite\Exception\FavoriteAlreadyExists;
use Domain\Favorite\Repository\FavoriteRepository;
use Domain\Favorite\ValueObject\FavoriteId;
use Domain\Gif\ValueObject\GifId;
use Domain\User\ValueObject\UserId;
use Illuminate\Database\UniqueConstraintViolationException;
use Infrastructure\Persistence\Eloquent\Models\FavoriteModel;

final class EloquentFavoriteRepository implements FavoriteRepository
{
    public function save(Favorite $favorite): Favorite
    {
        try {
            $model = FavoriteModel::query()->create([
                'user_id' => $favorite->userId()->value(),
                'gif_id' => $favorite->gifId()->value(),
                'alias' => $favorite->alias()->value(),
            ]);
        } catch (UniqueConstraintViolationException) {
            // The DB unique index is the source of truth under concurrency: turn a
            // duplicate insert into the domain conflict (mapped to 409) rather than 500.
            throw FavoriteAlreadyExists::forUserAndGif($favorite->userId(), $favorite->gifId());
        }

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
