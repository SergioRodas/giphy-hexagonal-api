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
}
