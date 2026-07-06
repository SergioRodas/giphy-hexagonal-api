<?php

declare(strict_types=1);

namespace Infrastructure\Auth;

use Domain\Auth\Contract\PasswordHasher;
use Illuminate\Contracts\Hashing\Hasher;

/**
 * Adapts Laravel's Hasher to the domain {@see PasswordHasher} port.
 */
final readonly class LaravelPasswordHasher implements PasswordHasher
{
    public function __construct(private Hasher $hasher)
    {
    }

    public function verify(string $plainPassword, string $hashedPassword): bool
    {
        return $this->hasher->check($plainPassword, $hashedPassword);
    }
}
