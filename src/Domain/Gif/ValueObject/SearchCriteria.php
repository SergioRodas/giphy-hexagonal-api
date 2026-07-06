<?php

declare(strict_types=1);

namespace Domain\Gif\ValueObject;

use InvalidArgumentException;

/**
 * Encapsulates the filters accepted by the GIF search use case and enforces
 * the boundaries imposed by the GIPHY API (limit 1..50, offset >= 0).
 */
final readonly class SearchCriteria
{
    public const int DEFAULT_LIMIT = 25;
    public const int MAX_LIMIT = 50;

    public function __construct(
        private string $query,
        private int $limit = self::DEFAULT_LIMIT,
        private int $offset = 0,
    ) {
        if (trim($query) === '') {
            throw new InvalidArgumentException('Search query is required.');
        }

        if ($limit < 1 || $limit > self::MAX_LIMIT) {
            throw new InvalidArgumentException(
                sprintf('Limit must be between 1 and %d.', self::MAX_LIMIT)
            );
        }

        if ($offset < 0) {
            throw new InvalidArgumentException('Offset must be zero or greater.');
        }
    }

    /**
     * Build criteria from nullable transport input, applying defaults.
     */
    public static function fromNullable(string $query, ?int $limit, ?int $offset): self
    {
        return new self(
            $query,
            $limit ?? self::DEFAULT_LIMIT,
            $offset ?? 0,
        );
    }

    public function query(): string
    {
        return trim($this->query);
    }

    public function limit(): int
    {
        return $this->limit;
    }

    public function offset(): int
    {
        return $this->offset;
    }
}
