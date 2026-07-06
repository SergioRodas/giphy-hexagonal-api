<?php

declare(strict_types=1);

namespace Domain\Auth\Contract;

use Domain\Auth\ValueObject\AuthToken;
use Domain\User\Entity\User;

/**
 * Port that issues OAuth2 access tokens for an authenticated user.
 * The concrete adapter (Passport) lives in the infrastructure layer.
 */
interface TokenIssuer
{
    public function issueFor(User $user): AuthToken;
}
