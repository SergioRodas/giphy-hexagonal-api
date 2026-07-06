<?php

declare(strict_types=1);

namespace Application\Auth\Login;

use Domain\Auth\Contract\PasswordHasher;
use Domain\Auth\Contract\TokenIssuer;
use Domain\Auth\Exception\InvalidCredentials;
use Domain\Auth\ValueObject\AuthToken;
use Domain\Shared\ValueObject\Email;
use Domain\User\Repository\UserRepository;
use InvalidArgumentException;

/**
 * Authenticates a user by e-mail/password and, on success, issues an OAuth2
 * access token. The business rule (verify credentials, then mint a token) lives
 * here and depends only on domain ports, so it is fully unit-testable.
 */
final readonly class LoginUseCase
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwordHasher,
        private TokenIssuer $tokenIssuer,
    ) {
    }

    /**
     * @throws InvalidCredentials when the e-mail is unknown or the password is wrong.
     */
    public function execute(LoginCommand $command): AuthToken
    {
        try {
            $email = new Email($command->email);
        } catch (InvalidArgumentException) {
            // A malformed e-mail can never match a stored account.
            throw InvalidCredentials::create();
        }

        $user = $this->users->findByEmail($email);

        if ($user === null || ! $this->passwordHasher->verify($command->password, $user->hashedPassword())) {
            throw InvalidCredentials::create();
        }

        return $this->tokenIssuer->issueFor($user);
    }
}
