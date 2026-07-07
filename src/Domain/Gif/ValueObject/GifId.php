<?php

declare(strict_types=1);

namespace Domain\Gif\ValueObject;

use Domain\Shared\Exception\InvalidInput;

/**
 * Identity of a GIPHY GIF.
 *
 * NOTE: The challenge describes the GIF id as "numeric", but the GIPHY API
 * actually issues alphanumeric identifiers (e.g. "Ev477g37MJORyOWfdG").
 * We honour the real provider contract and accept an alphanumeric string,
 * which is documented as a deliberate deviation in the README.
 */
final readonly class GifId
{
    public function __construct(private string $value)
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw InvalidInput::because('GifId cannot be empty.');
        }

        if (! preg_match('/^[A-Za-z0-9]+$/', $normalized)) {
            throw InvalidInput::because('GifId must be alphanumeric.');
        }
    }

    public function value(): string
    {
        return trim($this->value);
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
