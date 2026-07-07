<?php

declare(strict_types=1);

namespace Domain\Favorite\Exception;

use Domain\Shared\Exception\DomainException;

/**
 * A principal may only manage favorites for their own account.
 */
final class FavoriteOwnershipViolation extends DomainException
{
    public static function create(): self
    {
        return new self('You can only save favorites for your own account.');
    }
}
