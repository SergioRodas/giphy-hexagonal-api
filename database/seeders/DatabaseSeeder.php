<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Infrastructure\Persistence\Eloquent\Models\UserModel;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a demo account so the API can be
     * exercised straight away (see README / Postman collection).
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
    }
}
