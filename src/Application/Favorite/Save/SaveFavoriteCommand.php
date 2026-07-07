<?php

declare(strict_types=1);

namespace Application\Favorite\Save;

/**
 * Input DTO for the Save Favorite use case.
 *
 * Carries both the target user (from the request body, per the brief) and the
 * authenticated principal, so the ownership rule is enforced by the use case
 * itself rather than by any particular transport adapter.
 */
final readonly class SaveFavoriteCommand
{
    public function __construct(
        public string $gifId,
        public string $alias,
        public int $userId,
        public int $authenticatedUserId,
    ) {}
}
