<?php

namespace Tests\Feature;

use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_matching_media(): void
    {
        Media::factory()->create(['title' => 'Interstellar']);
        Media::factory()->create(['title' => 'The Bear']);

        $response = $this->getJson('/api/v1/search?q=inter');

        $response->assertOk();
        $this->assertCount(1, $response->json('data.media'));
        $this->assertEquals('Interstellar', $response->json('data.media.0.title'));
    }

    public function test_search_requires_minimum_query_length(): void
    {
        $response = $this->getJson('/api/v1/search?q=a');

        $response->assertOk()->assertJsonPath('data.media', []);
    }

    public function test_media_api_lists_and_filters_by_type(): void
    {
        Media::factory()->create(['media_type' => Media::TYPE_MOVIE]);
        Media::factory()->create(['media_type' => Media::TYPE_SERIES]);

        $response = $this->getJson('/api/v1/media?type=movie');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }
}
