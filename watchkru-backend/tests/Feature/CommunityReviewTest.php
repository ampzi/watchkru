<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_review_a_community_once(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/communities/{$community->id}/reviews", [
                'rating' => 5,
                'content' => 'انجمن بسیار فعال و مفیدی است.',
            ])
            ->assertCreated();

        $this->assertDatabaseCount('community_reviews', 1);
    }

    public function test_user_cannot_review_the_same_community_twice(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson("/api/v1/communities/{$community->id}/reviews", [
            'rating' => 4,
            'content' => 'خوب است.',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/communities/{$community->id}/reviews", [
            'rating' => 5,
            'content' => 'دوباره نظر می‌دهم.',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('community_reviews', 1);
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson("/api/v1/communities/{$community->id}/reviews", [
            'rating' => 9,
            'content' => 'نامعتبر',
        ])->assertStatus(422);
    }
}
