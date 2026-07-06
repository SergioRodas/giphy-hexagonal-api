<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Infrastructure\Persistence\Eloquent\Models\UserModel;
use Laravel\Passport\ClientRepository;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Idempotent: ensures a demo account (see README / Postman) and a Passport
     * personal-access client (required to issue OAuth2 tokens at login) exist.
     */
    public function run(): void
    {
        UserModel::query()->updateOrCreate(
            ['email' => 'demo@giphy-hexagonal.test'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
            ],
        );

        $this->ensurePersonalAccessClient();
    }

    private function ensurePersonalAccessClient(): void
    {
        $clients = app(ClientRepository::class);
        $provider = (string) config('auth.guards.api.provider', 'users');

        try {
            $clients->personalAccessClient($provider);
        } catch (RuntimeException) {
            $clients->createPersonalAccessGrantClient(
                config('app.name').' Personal Access Client'
            );
        }
    }
}
