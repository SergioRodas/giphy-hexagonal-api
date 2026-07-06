<?php

declare(strict_types=1);

namespace Tests\Support;

use Domain\Auth\Contract\PasswordHasher;

/**
 * Test double that treats the "hashed" value as the plain password, so a user
 * seeded with hashedPassword "secret" is verified by the plain password "secret".
 */
final class FakePasswordHasher implements PasswordHasher
{
    public function verify(string $plainPassword, string $hashedPassword): bool
    {
        return $plainPassword === $hashedPassword;
    }
}
