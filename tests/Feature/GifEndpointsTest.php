<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Infrastructure\Persistence\Eloquent\Models\UserModel;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class GifEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = UserModel::factory()->create();
    }

    #[Test]
    public function it_searches_gifs_and_maps_the_provider_payload(): void
    {
        Http::fake(['*/gifs/search*' => Http::response($this->searchPayload(), 200)]);
        Passport::actingAs($this->user);

        $this->getJson('/api/gifs/search?query=cats&limit=2')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'title', 'url', 'rating', 'username', 'images' => ['original', 'preview']]],
                'pagination' => ['total_count', 'count', 'offset'],
            ])
            ->assertJsonPath('data.0.id', 'Ev477g37MJORyOWfdG')
            ->assertJsonPath('pagination.total_count', 500);
    }

    #[Test]
    public function searching_requires_authentication(): void
    {
        $this->getJson('/api/gifs/search?query=cats')->assertStatus(401);
    }

    #[Test]
    public function searching_without_a_query_returns_422(): void
    {
        Passport::actingAs($this->user);

        $this->getJson('/api/gifs/search')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['query']);
    }

    #[Test]
    public function it_shows_a_single_gif_by_id(): void
    {
        Http::fake(['*/gifs/*' => Http::response($this->showPayload(), 200)]);
        Passport::actingAs($this->user);

        $this->getJson('/api/gifs/Ev477g37MJORyOWfdG')
            ->assertOk()
            ->assertJsonPath('data.id', 'Ev477g37MJORyOWfdG')
            ->assertJsonPath('data.title', 'Cat Meme GIF');
    }

    #[Test]
    public function it_returns_404_when_the_provider_has_no_such_gif(): void
    {
        Http::fake(['*/gifs/*' => Http::response(['meta' => ['status' => 404, 'msg' => 'Not Found']], 404)]);
        Passport::actingAs($this->user);

        $this->getJson('/api/gifs/missing123')
            ->assertStatus(404)
            ->assertJsonPath('error', 'gif_not_found');
    }

    #[Test]
    public function a_malformed_gif_id_is_rejected_with_422(): void
    {
        Passport::actingAs($this->user);

        // The GifId value object guards the format; no provider call is made.
        $this->getJson('/api/gifs/not..a--valid__id!')
            ->assertStatus(422)
            ->assertJsonPath('error', 'invalid_input');
    }

    #[Test]
    public function it_returns_502_when_the_provider_is_unavailable(): void
    {
        Http::fake(['*/gifs/search*' => Http::response('gateway error', 500)]);
        Passport::actingAs($this->user);

        $this->getJson('/api/gifs/search?query=cats')
            ->assertStatus(502)
            ->assertJsonPath('error', 'gif_provider_unavailable');
    }

    /**
     * @return array<string, mixed>
     */
    private function searchPayload(): array
    {
        return [
            'data' => [$this->gifResource()],
            'pagination' => ['total_count' => 500, 'count' => 1, 'offset' => 0],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function showPayload(): array
    {
        return ['data' => $this->gifResource(), 'meta' => ['status' => 200]];
    }

    /**
     * @return array<string, mixed>
     */
    private function gifResource(): array
    {
        return [
            'id' => 'Ev477g37MJORyOWfdG',
            'title' => 'Cat Meme GIF',
            'url' => 'https://giphy.com/gifs/Ev477g37MJORyOWfdG',
            'rating' => 'g',
            'username' => 'byomid',
            'images' => [
                'original' => ['url' => 'https://media.giphy.com/original.gif', 'width' => '480', 'height' => '476'],
                'fixed_height_small' => ['url' => 'https://media.giphy.com/small.gif', 'width' => '100', 'height' => '99'],
            ],
        ];
    }
}
