<?php

declare(strict_types=1);

namespace Domain\User\ValueObject;

use Domain\Shared\Exception\InvalidInput;

/**
 * Identity of a User. Backed by a positive integer to match the persistence key.
 */
final readonly class UserId
{
    public function __construct(private int $value)
    {
        if ($value <= 0) {
            throw InvalidInput::because('UserId must be a positive integer.');
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
