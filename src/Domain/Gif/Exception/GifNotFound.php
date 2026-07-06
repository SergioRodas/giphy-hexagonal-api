<?php

declare(strict_types=1);

namespace Domain\Gif\Exception;

use Domain\Shared\Exception\DomainException;
use Domain\Gif\ValueObject\GifId;

final class GifNotFound extends DomainException
{
    public static function withId(GifId $id): self
    {
        return new self(sprintf('GIF with id "%s" was not found.', $id->value()));
    }

    public function statusCode(): int
    {
        return 404;
    }
}
