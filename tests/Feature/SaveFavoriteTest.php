<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infrastructure\Persistence\Eloquent\Models\UserModel;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SaveFavoriteTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = UserModel::factory()->create();
    }

    #[Test]
    public function it_stores_a_favorite_and_returns_201(): void
    {
        Passport::actingAs($this->user);

        $this->postJson('/api/favorites', [
            'gif_id' => 'Ev477g37MJORyOWfdG',
            'alias' => 'My favourite cat',
            'user_id' => $this->user->id,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.gif_id', 'Ev477g37MJORyOWfdG')
            ->assertJsonPath('data.alias', 'My favourite cat');

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'gif_id' => 'Ev477g37MJORyOWfdG',
            'alias' => 'My favourite cat',
        ]);
    }

    #[Test]
    public function it_rejects_a_duplicate_favorite_with_409(): void
    {
        Passport::actingAs($this->user);

        $payload = ['gif_id' => 'Ev477g37MJORyOWfdG', 'alias' => 'cat', 'user_id' => $this->user->id];

        $this->postJson('/api/favorites', $payload)->assertStatus(201);
        $this->postJson('/api/favorites', $payload)
            ->assertStatus(409)
            ->assertJsonPath('error', 'favorite_already_exists');
    }

    #[Test]
    public function it_forbids_saving_a_favorite_for_another_user(): void
    {
        Passport::actingAs($this->user);

        // A token holder cannot create favorites owned by a different user (IDOR).
        $this->postJson('/api/favorites', [
            'gif_id' => 'Ev477g37MJORyOWfdG',
            'alias' => 'cat',
            'user_id' => $this->user->id + 1,
        ])->assertStatus(403)->assertJsonPath('error', 'forbidden');
    }

    #[Test]
    public function saving_requires_authentication(): void
    {
        $this->postJson('/api/favorites', [
            'gif_id' => 'Ev477g37MJORyOWfdG',
            'alias' => 'cat',
            'user_id' => $this->user->id,
        ])->assertStatus(401);
    }
}
