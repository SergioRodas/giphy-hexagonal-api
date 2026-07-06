<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Application\Auth\Login\LoginCommand;
use Application\Auth\Login\LoginUseCase;
use Domain\Auth\Exception\InvalidCredentials;
use Domain\Shared\ValueObject\Email;
use Domain\User\Entity\User;
use Domain\User\ValueObject\UserId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakePasswordHasher;
use Tests\Support\FakeTokenIssuer;
use Tests\Support\InMemoryUserRepository;

final class LoginUseCaseTest extends TestCase
{
    private InMemoryUserRepository $users;
    private FakeTokenIssuer $tokenIssuer;
    private LoginUseCase $useCase;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->users->add(new User(
            new UserId(1),
            'Demo User',
            new Email('demo@example.com'),
            'secret', // the FakePasswordHasher treats the hash as the plain text
        ));

        $this->tokenIssuer = new FakeTokenIssuer('issued-token');
        $this->useCase = new LoginUseCase($this->users, new FakePasswordHasher(), $this->tokenIssuer);
    }

    #[Test]
    public function it_issues_a_token_for_valid_credentials(): void
    {
        $token = $this->useCase->execute(new LoginCommand('demo@example.com', 'secret'));

        $this->assertSame('issued-token', $token->accessToken());
        $this->assertSame(1, $this->tokenIssuer->issuedFor?->id()->value());
    }

    #[Test]
    public function it_rejects_an_unknown_email(): void
    {
        $this->expectException(InvalidCredentials::class);

        $this->useCase->execute(new LoginCommand('nobody@example.com', 'secret'));
    }

    #[Test]
    public function it_rejects_a_wrong_password(): void
    {
        $this->expectException(InvalidCredentials::class);

        $this->useCase->execute(new LoginCommand('demo@example.com', 'wrong'));
    }

    #[Test]
    public function it_treats_a_malformed_email_as_invalid_credentials(): void
    {
        $this->expectException(InvalidCredentials::class);

        $this->useCase->execute(new LoginCommand('not-an-email', 'secret'));
    }
}
