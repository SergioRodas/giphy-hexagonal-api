<?php

declare(strict_types=1);

namespace Application\Favorite\Save;

/**
 * Input DTO for the Save Favorite use case.
 */
final readonly class SaveFavoriteCommand
{
    public function __construct(
        public string $gifId,
        public string $alias,
        public int $userId,
    ) {
    }
}
