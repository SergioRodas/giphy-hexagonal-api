<?php

declare(strict_types=1);

namespace Domain\Favorite\ValueObject;

use InvalidArgumentException;

final readonly class FavoriteId
{
    public function __construct(private int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('FavoriteId must be a positive integer.');
        }
    }

    public function value(): int
    {
        return $this->value;
    }
}
