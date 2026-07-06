<?php

declare(strict_types=1);

namespace Domain\Gif\Exception;

use Domain\Shared\Exception\DomainException;
use Throwable;

final class GifProviderUnavailable extends DomainException
{
    public static function create(?Throwable $previous = null): self
    {
        return new self('The GIF provider is currently unavailable.', 0, $previous);
    }

    /**
     * We act as a gateway to GIPHY; upstream failures surface as 502 Bad Gateway.
     */
    public function statusCode(): int
    {
        return 502;
    }
}
