<?php

declare(strict_types=1);

namespace Domain\Favorite\Exception;

use Domain\Gif\ValueObject\GifId;
use Domain\Shared\Exception\DomainException;
use Domain\User\ValueObject\UserId;

final class FavoriteAlreadyExists extends DomainException
{
    public static function forUserAndGif(UserId $userId, GifId $gifId): self
    {
        return new self(sprintf(
            'User %d has already saved GIF "%s" as favorite.',
            $userId->value(),
            $gifId->value(),
        ));
    }

    public function statusCode(): int
    {
        return 409;
    }
}
