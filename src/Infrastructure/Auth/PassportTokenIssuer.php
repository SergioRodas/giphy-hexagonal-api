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
 * The token lifetime is governed globally by Passport::personalAccessTokensExpireIn()
 * configured in the service provider, honouring the 30-minute requirement.
 */
final class PassportTokenIssuer implements TokenIssuer
{
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
            : (new DateTimeImmutable)->modify(
                sprintf('+%d minutes', (int) config('tokens.access_token_ttl'))
            );

        return new AuthToken($result->accessToken, $expiration);
    }
}
