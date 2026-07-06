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
    use RefreshDatabase;
    use InteractsWithPassport;

    #[Test]
    public function it_logs_the_login_interaction_and_redacts_the_password(): void
    {
        $this->ensurePassportReady();
        UserModel::factory()->create(['email' => 'demo@example.com']);

        $this->postJson('/api/login', ['email' => 'demo@example.com', 'password' => 'password'])
            ->assertOk();

        $log = RequestLogModel::query()->where('service', 'auth.login')->firstOrFail();

        $this->assertSame('POST', $log->method);
        $this->assertSame(200, $log->status_code);
        $this->assertNull($log->user_id); // login runs before authentication
        $this->assertNotNull($log->ip_address);
        $this->assertSame('[REDACTED]', $log->request_body['password']);
        $this->assertArrayHasKey('access_token', $log->response_body);
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
}
