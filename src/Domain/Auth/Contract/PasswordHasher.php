<?php

declare(strict_types=1);

namespace Domain\Auth\Contract;

/**
 * Port that abstracts password hashing/verification away from the domain.
 */
interface PasswordHasher
{
    public function verify(string $plainPassword, string $hashedPassword): bool;
}
