<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use Domain\Auth\Exception\InvalidCredentials;
use Domain\Favorite\Exception\FavoriteAlreadyExists;
use Domain\Gif\Exception\GifNotFound;
use Domain\Gif\Exception\GifProviderUnavailable;
use Domain\Gif\ValueObject\GifId;
use Domain\Shared\Exception\DomainException;
use Domain\User\Exception\UserNotFound;
use Domain\User\ValueObject\UserId;
use Infrastructure\Http\Exception\DomainExceptionMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DomainExceptionMapperTest extends TestCase
{
    /**
     * @return array<string, array{0: DomainException, 1: int, 2: string}>
     */
    public static function cases(): array
    {
        return [
            'invalid credentials -> 401' => [InvalidCredentials::create(), 401, 'invalid_credentials'],
            'gif not found -> 404' => [GifNotFound::withId(new GifId('abc123')), 404, 'gif_not_found'],
            'favorite conflict -> 409' => [FavoriteAlreadyExists::forUserAndGif(new UserId(1), new GifId('abc123')), 409, 'favorite_already_exists'],
            'provider down -> 502' => [GifProviderUnavailable::create(), 502, 'gif_provider_unavailable'],
            'user not found -> 422' => [UserNotFound::withId(new UserId(9)), 422, 'user_not_found'],
        ];
    }

    #[Test]
    #[DataProvider('cases')]
    public function it_maps_domain_exceptions_to_status_and_error_code(
        DomainException $exception,
        int $expectedStatus,
        string $expectedCode,
    ): void {
        $mapper = new DomainExceptionMapper;

        $this->assertSame($expectedStatus, $mapper->statusCode($exception));
        $this->assertSame($expectedCode, $mapper->errorCode($exception));
    }
}
