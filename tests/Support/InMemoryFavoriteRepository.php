<?php

declare(strict_types=1);

namespace Tests\Support;

use DateTimeImmutable;
use Domain\Favorite\Entity\Favorite;
use Domain\Favorite\Repository\FavoriteRepository;
use Domain\Favorite\ValueObject\FavoriteId;
use Domain\Gif\ValueObject\GifId;
use Domain\User\ValueObject\UserId;

/**
 * In-memory FavoriteRepository used to unit-test the SaveFavorite use case.
 */
final class InMemoryFavoriteRepository implements FavoriteRepository
{
    /** @var array<int, Favorite> */
    private array $favorites = [];

    private int $nextId = 1;

    public function save(Favorite $favorite): Favorite
    {
        $id = $this->nextId++;

        $stored = Favorite::reconstitute(
            new FavoriteId($id),
            $favorite->userId(),
            $favorite->gifId(),
            $favorite->alias(),
            new DateTimeImmutable('2026-01-01T00:00:00+00:00'),
        );

        $this->favorites[$id] = $stored;

        return $stored;
    }

    public function existsForUserAndGif(UserId $userId, GifId $gifId): bool
    {
        foreach ($this->favorites as $favorite) {
            if ($favorite->userId()->equals($userId) && $favorite->gifId()->equals($gifId)) {
                return true;
            }
        }

        return false;
    }

    public function count(): int
    {
        return count($this->favorites);
    }
}
