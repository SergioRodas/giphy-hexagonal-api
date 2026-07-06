<?php

declare(strict_types=1);

namespace Application\Gif\Show;

/**
 * Input DTO for the Get GIF by id use case.
 */
final readonly class GetGifByIdQuery
{
    public function __construct(public string $id)
    {
    }
}
