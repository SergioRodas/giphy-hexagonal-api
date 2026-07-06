<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObject;

use InvalidArgumentException;

/**
 * Immutable e-mail address. Guarantees any Email instance is syntactically valid.
 */
final readonly class Email
{
    public function __construct(private string $value)
    {
        $normalized = trim($value);

        if ($normalized === '' || filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid e-mail address.', $value));
        }
    }

    public function value(): string
    {
        return strtolower(trim($this->value));
    }

    public function equals(self $other): bool
    {
        return $this->value() === $other->value();
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
