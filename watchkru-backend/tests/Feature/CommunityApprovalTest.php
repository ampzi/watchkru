<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityCategory;
use App\Models\Media;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_a_pending_community(): void
    {
        $admin = User::factory()->admin()->create();
        $community = Community::factory()->create(['verification_status' => Community::VERIFICATION_PENDING]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/communities/{$community->id}/approve");

        $response->assertOk();
        $this->assertDatabaseHas('communities', [
            'id' => $community->id,
            'verification_status' => Community::VERIFICATION_VERIFIED,
        ]);
    }

    public function test_regular_user_cannot_approve_a_community(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create(['verification_status' => Community::VERIFICATION_PENDING]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/admin/communities/{$community->id}/approve")
            ->assertStatus(403);
    }

    public function test_admin_can_reject_a_community_with_a_note(): void
    {
        $admin = User::factory()->admin()->create();
        $community = Community::factory()->create(['verification_status' => Community::VERIFICATION_PENDING]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/communities/{$community->id}/reject", [
                'admin_note' => 'لینک نامعتبر است.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('communities', [
            'id' => $community->id,
            'verification_status' => Community::VERIFICATION_REJECTED,
        ]);
    }
}
