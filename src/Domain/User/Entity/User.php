<?php

declare(strict_types=1);

namespace Domain\User\Entity;

use Domain\Shared\ValueObject\Email;
use Domain\User\ValueObject\UserId;

/**
 * User aggregate root.
 *
 * Holds the already-hashed password so credential verification can be performed
 * by the application layer through the {@see \Domain\Auth\Contract\PasswordHasher}
 * port, keeping the domain free of any framework hashing concern.
 */
final readonly class User
{
    public function __construct(
        private UserId $id,
        private string $name,
        private Email $email,
        private string $hashedPassword,
    ) {
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function hashedPassword(): string
    {
        return $this->hashedPassword;
    }
}
