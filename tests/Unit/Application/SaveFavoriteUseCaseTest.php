<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Application\Favorite\Save\SaveFavoriteCommand;
use Application\Favorite\Save\SaveFavoriteUseCase;
use Domain\Favorite\Exception\FavoriteAlreadyExists;
use Domain\Favorite\Exception\FavoriteOwnershipViolation;
use Domain\Shared\ValueObject\Email;
use Domain\User\Entity\User;
use Domain\User\Exception\UserNotFound;
use Domain\User\ValueObject\UserId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryFavoriteRepository;
use Tests\Support\InMemoryUserRepository;

final class SaveFavoriteUseCaseTest extends TestCase
{
    private InMemoryFavoriteRepository $favorites;

    private SaveFavoriteUseCase $useCase;

    protected function setUp(): void
    {
        $users = new InMemoryUserRepository;
        $users->add(new User(new UserId(1), 'Demo', new Email('demo@example.com'), 'secret'));

        $this->favorites = new InMemoryFavoriteRepository;
        $this->useCase = new SaveFavoriteUseCase($this->favorites, $users);
    }

    #[Test]
    public function it_persists_a_favorite_for_the_authenticated_user(): void
    {
        $favorite = $this->useCase->execute(
            new SaveFavoriteCommand('abc123', 'My cat', userId: 1, authenticatedUserId: 1)
        );

        $this->assertTrue($favorite->isPersisted());
        $this->assertSame('abc123', $favorite->gifId()->value());
        $this->assertSame('My cat', $favorite->alias()->value());
        $this->assertSame(1, $this->favorites->count());
    }

    #[Test]
    public function it_rejects_saving_a_favorite_for_another_user(): void
    {
        $this->expectException(FavoriteOwnershipViolation::class);

        $this->useCase->execute(
            new SaveFavoriteCommand('abc123', 'My cat', userId: 2, authenticatedUserId: 1)
        );
    }

    #[Test]
    public function ownership_violations_do_not_persist_anything(): void
    {
        try {
            $this->useCase->execute(
                new SaveFavoriteCommand('abc123', 'My cat', userId: 2, authenticatedUserId: 1)
            );
        } catch (FavoriteOwnershipViolation) {
            // expected
        }

        $this->assertSame(0, $this->favorites->count());
    }

    #[Test]
    public function it_rejects_a_favorite_for_a_non_existent_user(): void
    {
        $this->expectException(UserNotFound::class);

        $this->useCase->execute(
            new SaveFavoriteCommand('abc123', 'My cat', userId: 999, authenticatedUserId: 999)
        );
    }

    #[Test]
    public function it_prevents_the_same_user_from_saving_the_same_gif_twice(): void
    {
        $this->useCase->execute(new SaveFavoriteCommand('abc123', 'first', userId: 1, authenticatedUserId: 1));

        $this->expectException(FavoriteAlreadyExists::class);

        $this->useCase->execute(new SaveFavoriteCommand('abc123', 'second', userId: 1, authenticatedUserId: 1));
    }
}
