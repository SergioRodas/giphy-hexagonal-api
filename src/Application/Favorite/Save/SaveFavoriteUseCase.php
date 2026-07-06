<?php

declare(strict_types=1);

namespace Application\Favorite\Save;

use Domain\Favorite\Entity\Favorite;
use Domain\Favorite\Exception\FavoriteAlreadyExists;
use Domain\Favorite\Repository\FavoriteRepository;
use Domain\Favorite\ValueObject\Alias;
use Domain\Gif\ValueObject\GifId;
use Domain\User\Exception\UserNotFound;
use Domain\User\Repository\UserRepository;
use Domain\User\ValueObject\UserId;

/**
 * Stores a favorite GIF for a user.
 *
 * Business rules enforced here:
 *  - the referenced user must exist;
 *  - a user cannot save the same GIF twice (idempotency guard).
 */
final readonly class SaveFavoriteUseCase
{
    public function __construct(
        private FavoriteRepository $favorites,
        private UserRepository $users,
    ) {}

    /**
     * @throws UserNotFound when the referenced user does not exist.
     * @throws FavoriteAlreadyExists when the user already saved this GIF.
     */
    public function execute(SaveFavoriteCommand $command): Favorite
    {
        $userId = new UserId($command->userId);

        if (! $this->users->exists($userId)) {
            throw UserNotFound::withId($userId);
        }

        $gifId = new GifId($command->gifId);

        if ($this->favorites->existsForUserAndGif($userId, $gifId)) {
            throw FavoriteAlreadyExists::forUserAndGif($userId, $gifId);
        }

        $favorite = Favorite::create($userId, $gifId, new Alias($command->alias));

        return $this->favorites->save($favorite);
    }
}
