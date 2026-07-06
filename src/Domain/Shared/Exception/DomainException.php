<?php

declare(strict_types=1);

namespace Domain\Shared\Exception;

use RuntimeException;

/**
 * Base class for every expected business-rule violation.
 *
 * These exceptions represent domain outcomes (invalid credentials, missing
 * resource, conflict, ...) rather than programming errors, so the HTTP layer
 * can translate them into the appropriate status code via {@see statusCode()}.
 */
abstract class DomainException extends RuntimeException
{
    /**
     * HTTP status the transport layer should use when this error escapes.
     * Defaults to 422 (Unprocessable Entity); subclasses override as needed.
     */
    public function statusCode(): int
    {
        return 422;
    }

    /**
     * Machine-readable error code, useful for API clients.
     */
    public function errorCode(): string
    {
        $parts = explode('\\', static::class);

        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', end($parts)));
    }
}
