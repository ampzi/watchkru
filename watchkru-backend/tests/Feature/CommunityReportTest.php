<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_report_a_community(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/communities/{$community->id}/report", [
                'report_type' => 'broken_link',
                'description' => 'لینک دیگر کار نمی‌کند.',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('community_reports', [
            'community_id' => $community->id,
            'report_type' => 'broken_link',
        ]);
    }

    public function test_report_type_must_be_valid(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/communities/{$community->id}/report", [
                'report_type' => 'not_a_real_type',
            ])
            ->assertStatus(422);
    }

    public function test_admin_can_resolve_a_report(): void
    {
        $admin = User::factory()->admin()->create();
        $community = Community::factory()->create();
        $report = \App\Models\CommunityReport::create([
            'community_id' => $community->id,
            'report_type' => 'spam',
            'status' => 'open',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/reports/{$report->id}", ['status' => 'resolved'])
            ->assertOk();

        $this->assertDatabaseHas('community_reports', ['id' => $report->id, 'status' => 'resolved']);
    }
}
