<?php

declare(strict_types=1);

namespace Domain\Shared\Exception;

/**
 * A value-object invariant was violated by caller-supplied input (empty query,
 * malformed id, oversized alias, ...).
 *
 * Being a DomainException subtype, it is rendered by the infrastructure
 * exception mapper (422) without any catch-all handler for generic SPL
 * exceptions — so framework/library InvalidArgumentExceptions keep surfacing
 * as the server errors they are.
 */
final class InvalidInput extends DomainException
{
    public static function because(string $reason): self
    {
        return new self($reason);
    }
}
