<?php

declare(strict_types=1);

namespace Tests\Support;

use Laravel\Passport\ClientRepository;
use RuntimeException;

/**
 * Ensures Passport can issue personal-access tokens during feature tests
 * (encryption keys + a personal-access client) after RefreshDatabase.
 */
trait InteractsWithPassport
{
    protected function ensurePassportReady(): void
    {
        if (! file_exists(storage_path('oauth-private.key'))) {
            $this->artisan('passport:keys', ['--force' => true]);
        }

        $clients = app(ClientRepository::class);
        $provider = (string) config('auth.guards.api.provider', 'users');

        try {
            $clients->personalAccessClient($provider);
        } catch (RuntimeException) {
            $clients->createPersonalAccessGrantClient('Test Personal Access Client');
        }
    }
}
