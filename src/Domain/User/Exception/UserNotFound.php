<?php

declare(strict_types=1);

namespace Domain\User\Exception;

use Domain\Shared\Exception\DomainException;
use Domain\User\ValueObject\UserId;

final class UserNotFound extends DomainException
{
    public static function withId(UserId $id): self
    {
        return new self(sprintf('User with id %d was not found.', $id->value()));
    }

    /**
     * The user is referenced from the request body (not the URL), so a
     * well-formed request that points to a non-existent user is unprocessable.
     */
    public function statusCode(): int
    {
        return 422;
    }
}
