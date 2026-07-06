<?php

declare(strict_types=1);

namespace Application\Auth\Login;

/**
 * Input DTO for the Login use case.
 */
final readonly class LoginCommand
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }
}
