<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityCategory;
use App\Models\Media;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunitySubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function makeMedia(): Media
    {
        return Media::factory()->create();
    }

    public function test_authenticated_user_can_submit_a_community(): void
    {
        $user = User::factory()->create();
        $platform = Platform::factory()->create(['slug' => 'telegram_group', 'name' => 'Telegram Group']);
        $category = CommunityCategory::factory()->create();
        $media = $this->makeMedia();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/communities', [
            'name' => 'Silo Fans',
            'platform' => 'telegram_group',
            'external_url' => 'https://t.me/silofans',
            'media_id' => $media->id,
            'community_category_id' => $category->id,
            'language' => 'fa',
            'description' => 'A friendly group for Silo fans.',
        ]);

        $response->assertCreated()->assertJsonPath('success', true);

        $this->assertDatabaseHas('communities', [
            'name' => 'Silo Fans',
            'verification_status' => Community::VERIFICATION_PENDING,
        ]);
        $this->assertDatabaseHas('community_submissions', ['user_id' => $user->id]);
    }

    public function test_guest_cannot_submit_a_community(): void
    {
        $platform = Platform::factory()->create(['slug' => 'telegram_group']);
        $category = CommunityCategory::factory()->create();
        $media = $this->makeMedia();

        $this->postJson('/api/v1/communities', [
            'name' => 'Silo Fans',
            'platform' => 'telegram_group',
            'external_url' => 'https://t.me/silofans',
            'media_id' => $media->id,
            'community_category_id' => $category->id,
        ])->assertStatus(401);
    }

    public function test_duplicate_url_is_rejected(): void
    {
        $user = User::factory()->create();
        $platform = Platform::factory()->create(['slug' => 'telegram_group']);
        $category = CommunityCategory::factory()->create();
        $media = $this->makeMedia();

        Community::factory()->create([
            'platform_id' => $platform->id,
            'community_category_id' => $category->id,
            'external_url' => 'https://t.me/silofans',
            'normalized_url' => Community::normalizeUrl('https://t.me/silofans'),
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/communities', [
            'name' => 'Another Silo Group',
            'platform' => 'telegram_group',
            // Same destination, different casing/www/trailing slash —
            // normalization must still catch it as the same URL.
            'external_url' => 'https://WWW.t.me/silofans/',
            'media_id' => $media->id,
            'community_category_id' => $category->id,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_invalid_url_scheme_is_rejected(): void
    {
        $user = User::factory()->create();
        $platform = Platform::factory()->create(['slug' => 'telegram_group']);
        $category = CommunityCategory::factory()->create();
        $media = $this->makeMedia();

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/communities', [
            'name' => 'Bad Link Group',
            'platform' => 'telegram_group',
            'external_url' => 'javascript:alert(1)',
            'media_id' => $media->id,
            'community_category_id' => $category->id,
        ])->assertStatus(422);
    }
}
