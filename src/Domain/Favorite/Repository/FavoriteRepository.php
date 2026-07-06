<?php

declare(strict_types=1);

namespace Domain\Favorite\Repository;

use Domain\Favorite\Entity\Favorite;
use Domain\Gif\ValueObject\GifId;
use Domain\User\ValueObject\UserId;

/**
 * Port to the Favorite persistence store. Implemented in the infrastructure layer.
 */
interface FavoriteRepository
{
    /**
     * Persist the favorite and return it with its generated identity.
     */
    public function save(Favorite $favorite): Favorite;

    public function existsForUserAndGif(UserId $userId, GifId $gifId): bool;
}
