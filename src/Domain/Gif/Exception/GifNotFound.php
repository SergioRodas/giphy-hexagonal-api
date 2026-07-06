<?php

declare(strict_types=1);

namespace Domain\Gif\Exception;

use Domain\Gif\ValueObject\GifId;
use Domain\Shared\Exception\DomainException;

final class GifNotFound extends DomainException
{
    public static function withId(GifId $id): self
    {
        return new self(sprintf('GIF with id "%s" was not found.', $id->value()));
    }
}
