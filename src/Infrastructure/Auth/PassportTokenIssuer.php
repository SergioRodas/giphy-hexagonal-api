<?php

declare(strict_types=1);

namespace Infrastructure\Auth;

use DateTimeImmutable;
use Domain\Auth\Contract\TokenIssuer;
use Domain\Auth\ValueObject\AuthToken;
use Domain\User\Entity\User;
use Infrastructure\Persistence\Eloquent\Models\UserModel;
use RuntimeException;

/**
 * Issues OAuth2 access tokens through Laravel Passport (the OAuth2 server).
 *
 * Passport can only mint personal-access tokens from an Eloquent model, while
 * the domain {@see User} is deliberately persistence-agnostic — so this adapter
 * re-hydrates the model by id (one indexed lookup per login). The token
 * lifetime is governed globally by Passport::personalAccessTokensExpireIn()
 * (see DomainServiceProvider); the injected TTL is only a fallback when the
 * Passport token carries no expiry.
 */
final readonly class PassportTokenIssuer implements TokenIssuer
{
    public function __construct(private int $ttlMinutes) {}

    public function issueFor(User $user): AuthToken
    {
        $model = UserModel::query()->find($user->id()->value());

        if ($model === null) {
            throw new RuntimeException('Cannot issue a token for a non-existent user.');
        }

        $result = $model->createToken('giphy-api');

        $expiresAt = $result->token->expires_at;

        $expiration = $expiresAt !== null
            ? DateTimeImmutable::createFromInterface($expiresAt)
            : (new DateTimeImmutable)->modify(sprintf('+%d minutes', $this->ttlMinutes));

        return new AuthToken($result->accessToken, $expiration);
    }
}
