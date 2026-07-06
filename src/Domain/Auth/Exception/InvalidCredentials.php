<?php

declare(strict_types=1);

namespace Domain\Auth\Exception;

use Domain\Shared\Exception\DomainException;

final class InvalidCredentials extends DomainException
{
    public static function create(): self
    {
        return new self('The provided credentials are incorrect.');
    }

    public function statusCode(): int
    {
        return 401;
    }
}
