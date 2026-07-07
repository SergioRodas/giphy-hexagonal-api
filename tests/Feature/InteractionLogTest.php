<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Infrastructure\Persistence\Eloquent\Models\RequestLogModel;
use Infrastructure\Persistence\Eloquent\Models\UserModel;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\InteractsWithPassport;
use Tests\TestCase;

final class InteractionLogTest extends TestCase
{
    use InteractsWithPassport;
    use RefreshDatabase;

    #[Test]
    public function it_logs_the_login_interaction_and_redacts_secrets(): void
    {
        $this->ensurePassportReady();
        UserModel::factory()->create(['email' => 'demo@example.com']);

        $this->postJson('/api/login', [
            'email' => 'demo@example.com',
            'password' => 'password',
            // Extra nested payload: redaction must reach any depth.
            'meta' => ['credentials' => ['password' => 'nested-secret']],
        ])->assertOk();

        $log = RequestLogModel::query()->where('service', 'auth.login')->firstOrFail();

        $this->assertSame('POST', $log->method);
        $this->assertSame(200, $log->status_code);
        $this->assertNull($log->user_id); // login runs before authentication
        $this->assertNotNull($log->ip_address);
        // Secrets are never stored in clear text (request password + issued token).
        $this->assertSame('[REDACTED]', $log->request_body['password']);
        $this->assertSame('[REDACTED]', $log->request_body['meta']['credentials']['password']);
        $this->assertSame('[REDACTED]', $log->response_body['access_token']);
    }

    #[Test]
    public function it_captures_the_authenticated_user_and_service(): void
    {
        $user = UserModel::factory()->create();
        Http::fake(['*/gifs/search*' => Http::response(['data' => [], 'pagination' => ['total_count' => 0, 'count' => 0, 'offset' => 0]], 200)]);
        Passport::actingAs($user);

        $this->getJson('/api/gifs/search?query=cats')->assertOk();

        $log = RequestLogModel::query()->where('service', 'gifs.search')->firstOrFail();

        $this->assertSame((int) $user->id, (int) $log->user_id);
        $this->assertSame(200, $log->status_code);
        $this->assertSame('cats', $log->request_body['query']);
    }

    #[Test]
    public function it_logs_rejected_unauthenticated_requests(): void
    {
        $this->getJson('/api/gifs/search?query=cats')->assertStatus(401);

        $log = RequestLogModel::query()->where('service', 'gifs.search')->firstOrFail();

        $this->assertSame(401, $log->status_code);
        $this->assertNull($log->user_id);
    }

    #[Test]
    public function it_logs_requests_that_match_no_route(): void
    {
        $this->getJson('/api/does-not-exist')->assertStatus(404);

        $this->assertDatabaseHas('request_logs', [
            'path' => 'api/does-not-exist',
            'status_code' => 404,
        ]);
    }
}
