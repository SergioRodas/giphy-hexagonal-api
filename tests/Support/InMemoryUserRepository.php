<?php

declare(strict_types=1);

namespace Tests\Support;

use Domain\Shared\ValueObject\Email;
use Domain\User\Entity\User;
use Domain\User\Repository\UserRepository;
use Domain\User\ValueObject\UserId;

/**
 * In-memory UserRepository used to unit-test use cases without a database.
 */
final class InMemoryUserRepository implements UserRepository
{
    /** @var array<int, User> */
    private array $users = [];

    public function add(User $user): void
    {
        $this->users[$user->id()->value()] = $user;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->email()->equals($email)) {
                return $user;
            }
        }

        return null;
    }

    public function findById(UserId $id): ?User
    {
        return $this->users[$id->value()] ?? null;
    }

    public function exists(UserId $id): bool
    {
        return isset($this->users[$id->value()]);
    }
}
