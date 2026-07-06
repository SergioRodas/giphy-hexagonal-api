<?php

declare(strict_types=1);

namespace Domain\Favorite\Entity;

use DateTimeImmutable;
use Domain\Favorite\ValueObject\Alias;
use Domain\Favorite\ValueObject\FavoriteId;
use Domain\Gif\ValueObject\GifId;
use Domain\User\ValueObject\UserId;
use LogicException;

/**
 * A GIF that a user has stored as favorite under a personal alias.
 */
final readonly class Favorite
{
    private function __construct(
        private ?FavoriteId $id,
        private UserId $userId,
        private GifId $gifId,
        private Alias $alias,
        private ?DateTimeImmutable $createdAt,
    ) {}

    /**
     * Create a brand-new favorite that has not been persisted yet.
     */
    public static function create(UserId $userId, GifId $gifId, Alias $alias): self
    {
        return new self(null, $userId, $gifId, $alias, null);
    }

    /**
     * Rebuild a favorite from its persisted state.
     */
    public static function reconstitute(
        FavoriteId $id,
        UserId $userId,
        GifId $gifId,
        Alias $alias,
        DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $userId, $gifId, $alias, $createdAt);
    }

    public function id(): FavoriteId
    {
        if ($this->id === null) {
            throw new LogicException('This favorite has not been persisted yet.');
        }

        return $this->id;
    }

    public function isPersisted(): bool
    {
        return $this->id !== null;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function gifId(): GifId
    {
        return $this->gifId;
    }

    public function alias(): Alias
    {
        return $this->alias;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
}
