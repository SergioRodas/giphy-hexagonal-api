<?php

declare(strict_types=1);

namespace Application\Auth\Login;

use Domain\Auth\Contract\PasswordHasher;
use Domain\Auth\Contract\TokenIssuer;
use Domain\Auth\Exception\InvalidCredentials;
use Domain\Auth\ValueObject\AuthToken;
use Domain\Shared\Exception\InvalidInput;
use Domain\Shared\ValueObject\Email;
use Domain\User\Repository\UserRepository;

/**
 * Authenticates a user by e-mail/password and, on success, issues an OAuth2
 * access token. The business rule (verify credentials, then mint a token) lives
 * here and depends only on domain ports, so it is fully unit-testable.
 */
final readonly class LoginUseCase
{
    /**
     * Bcrypt hash of a random sentinel, verified against when the e-mail is
     * unknown so both failure paths cost one hash comparison. Without it,
     * unknown accounts would respond measurably faster than wrong passwords,
     * enabling user enumeration through timing.
     */
    private const string DUMMY_HASH = '$2y$12$m5q3NrNlutsMZ.KoleIErOSYJFr6G4T6cUW9JFdROtomMfZL1rXC6';

    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwordHasher,
        private TokenIssuer $tokenIssuer,
    ) {}

    /**
     * @throws InvalidCredentials when the e-mail is unknown or the password is wrong.
     */
    public function execute(LoginCommand $command): AuthToken
    {
        try {
            $email = new Email($command->email);
        } catch (InvalidInput) {
            // A malformed e-mail can never match a stored account.
            throw InvalidCredentials::create();
        }

        $user = $this->users->findByEmail($email);

        $verified = $this->passwordHasher->verify(
            $command->password,
            $user?->hashedPassword() ?? self::DUMMY_HASH,
        );

        if ($user === null || ! $verified) {
            throw InvalidCredentials::create();
        }

        return $this->tokenIssuer->issueFor($user);
    }
}
