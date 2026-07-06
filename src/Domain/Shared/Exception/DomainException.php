<?php

declare(strict_types=1);

namespace Domain\Shared\Exception;

use RuntimeException;

/**
 * Base class for every expected business-rule violation (invalid credentials,
 * missing resource, conflict, ...).
 *
 * These are domain outcomes, not programming errors. The domain stays free of
 * any transport concern: mapping an exception type to an HTTP status/error code
 * is the responsibility of the infrastructure layer (a DomainExceptionMapper).
 */
abstract class DomainException extends RuntimeException {}
