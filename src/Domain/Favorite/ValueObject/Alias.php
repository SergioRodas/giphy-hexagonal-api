<?php

declare(strict_types=1);

namespace Domain\Favorite\ValueObject;

use Domain\Shared\Exception\InvalidInput;

/**
 * Human-friendly label a user assigns to a favorited GIF.
 */
final readonly class Alias
{
    public const int MAX_LENGTH = 100;

    public function __construct(private string $value)
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw InvalidInput::because('Alias is required.');
        }

        if (mb_strlen($normalized) > self::MAX_LENGTH) {
            throw InvalidInput::because(
                sprintf('Alias must not exceed %d characters.', self::MAX_LENGTH)
            );
        }
    }

    public function value(): string
    {
        return trim($this->value);
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
