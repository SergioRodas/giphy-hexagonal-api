<?php

declare(strict_types=1);

namespace Infrastructure\Providers;

use Domain\Audit\Repository\RequestLogRepository;
use Domain\Auth\Contract\PasswordHasher;
use Domain\Auth\Contract\TokenIssuer;
use Domain\Favorite\Repository\FavoriteRepository;
use Domain\Gif\Repository\GifRepository;
use Domain\User\Repository\UserRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Auth\LaravelPasswordHasher;
use Infrastructure\Auth\PassportTokenIssuer;
use Infrastructure\Giphy\GiphyGifMapper;
use Infrastructure\Giphy\GiphyGifRepository;
use Infrastructure\Persistence\Eloquent\EloquentFavoriteRepository;
use Infrastructure\Persistence\Eloquent\EloquentRequestLogRepository;
use Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Laravel\Passport\Passport;

/**
 * Composition root: binds every domain port to its infrastructure adapter and
 * configures the OAuth2 token lifetimes. This is the single place where the
 * application is wired to concrete technology.
 */
final class DomainServiceProvider extends ServiceProvider
{
    /**
     * Ports whose adapters can be autowired straight from the container.
     *
     * @var array<class-string, class-string>
     */
    public array $singletons = [
        UserRepository::class => EloquentUserRepository::class,
        FavoriteRepository::class => EloquentFavoriteRepository::class,
        RequestLogRepository::class => EloquentRequestLogRepository::class,
        PasswordHasher::class => LaravelPasswordHasher::class,
        TokenIssuer::class => PassportTokenIssuer::class,
    ];

    public function register(): void
    {
        // Share a single HTTP client factory so the Http facade (and Http::fake()
        // in tests) and the injected GIPHY adapter operate on the same instance.
        $this->app->singleton(HttpClient::class);

        // The GIPHY adapter needs configuration primitives, so it is wired by hand.
        $this->app->singleton(GifRepository::class, function (Application $app): GiphyGifRepository {
            /** @var array<string, mixed> $config */
            $config = (array) config('services.giphy');

            return new GiphyGifRepository(
                $app->make(HttpClient::class),
                $app->make(GiphyGifMapper::class),
                (string) ($config['key'] ?? ''),
                (string) ($config['base_url'] ?? 'https://api.giphy.com/v1'),
                (int) ($config['timeout'] ?? 10),
                (string) ($config['rating'] ?? 'g'),
                (string) ($config['lang'] ?? 'en'),
            );
        });
    }

    public function boot(): void
    {
        $ttlMinutes = (int) config('tokens.access_token_ttl');

        Passport::tokensExpireIn(now()->addMinutes($ttlMinutes));
        Passport::personalAccessTokensExpireIn(now()->addMinutes($ttlMinutes));
        Passport::refreshTokensExpireIn(now()->addMinutes($ttlMinutes)->addDay());
    }
}
