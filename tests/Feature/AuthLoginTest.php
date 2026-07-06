<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infrastructure\Persistence\Eloquent\Models\UserModel;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\InteractsWithPassport;
use Tests\TestCase;

final class AuthLoginTest extends TestCase
{
    use InteractsWithPassport;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePassportReady();
    }

    #[Test]
    public function it_issues_an_oauth2_token_that_expires_in_30_minutes(): void
    {
        UserModel::factory()->create(['email' => 'demo@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'demo@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token_type', 'access_token', 'expires_in', 'expires_at'])
            ->assertJsonPath('token_type', 'Bearer');

        $this->assertEqualsWithDelta(1800, $response->json('expires_in'), 10);
    }

    #[Test]
    public function it_rejects_wrong_credentials_with_401(): void
    {
        UserModel::factory()->create(['email' => 'demo@example.com']);

        $this->postJson('/api/login', ['email' => 'demo@example.com', 'password' => 'nope'])
            ->assertStatus(401)
            ->assertJsonPath('error', 'invalid_credentials');
    }

    #[Test]
    public function it_validates_the_payload_with_422(): void
    {
        $this->postJson('/api/login', ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
