<?php

declare(strict_types=1);

namespace Domain\User\Repository;

use Domain\Shared\ValueObject\Email;
use Domain\User\Entity\User;
use Domain\User\ValueObject\UserId;

/**
 * Port to the User persistence store. Implemented in the infrastructure layer.
 */
interface UserRepository
{
    public function findByEmail(Email $email): ?User;

    public function findById(UserId $id): ?User;

    public function exists(UserId $id): bool;
}
