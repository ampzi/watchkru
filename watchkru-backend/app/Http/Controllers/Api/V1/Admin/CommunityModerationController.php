<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommunityResource;
use App\Models\AuditLog;
use App\Models\Community;
use App\Models\CommunitySubmission;
use Illuminate\Http\Request;

class CommunityModerationController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $query = Community::withTrashed()->with(['platform', 'communityCategory', 'media', 'creator']);

        if ($status = $request->get('status')) {
            $query->where('verification_status', $status);
        }

        return $this->paginated(
            $query->latest()->paginate(min((int) $request->get('per_page', 20), 50)),
            CommunityResource::class
        );
    }

    public function update(Request $request, Community $community)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'external_url' => ['sometimes', 'url:http,https', 'max:2048'],
            'member_count' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'activity_status' => ['sometimes', 'in:active,inactive,unknown'],
        ]);

        $old = $community->only(array_keys($data));

        if (isset($data['external_url'])) {
            $data['normalized_url'] = Community::normalizeUrl($data['external_url']);
        }

        $community->update($data);
        AuditLog::record('community.update', $community, $old, $data);

        return $this->ok(new CommunityResource($community->fresh(['platform', 'communityCategory', 'media'])), 'انجمن به‌روزرسانی شد.');
    }

    public function approve(Request $request, Community $community)
    {
        $community->update([
            'verification_status' => Community::VERIFICATION_VERIFIED,
            'approved_by' => $request->user()->id,
            'last_checked_at' => now(),
        ]);

        CommunitySubmission::where('community_id', $community->id)
            ->update([
                'status' => CommunitySubmission::STATUS_APPROVED,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

        AuditLog::record('community.approve', $community);

        return $this->ok(new CommunityResource($community), 'انجمن تأیید شد.');
    }

    public function reject(Request $request, Community $community)
    {
        $note = $request->validate(['admin_note' => ['nullable', 'string', 'max:1000']])['admin_note'] ?? null;

        $community->update(['verification_status' => Community::VERIFICATION_REJECTED]);

        CommunitySubmission::where('community_id', $community->id)
            ->update([
                'status' => CommunitySubmission::STATUS_REJECTED,
                'admin_note' => $note,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

        AuditLog::record('community.reject', $community, null, ['admin_note' => $note]);

        return $this->ok(new CommunityResource($community), 'انجمن رد شد.');
    }

    public function markVerified(Request $request, Community $community)
    {
        $community->update([
            'verification_status' => Community::VERIFICATION_VERIFIED,
            'last_checked_at' => now(),
        ]);
        AuditLog::record('community.mark_verified', $community);

        return $this->ok(new CommunityResource($community), 'انجمن تأییدشده علامت‌گذاری شد.');
    }

    public function markInactive(Request $request, Community $community)
    {
        $community->update(['activity_status' => Community::ACTIVITY_INACTIVE, 'last_checked_at' => now()]);
        AuditLog::record('community.mark_inactive', $community);

        return $this->ok(new CommunityResource($community), 'انجمن غیرفعال علامت‌گذاری شد.');
    }

    public function markNeedsReview(Request $request, Community $community)
    {
        $community->update(['verification_status' => Community::VERIFICATION_NEEDS_REVIEW]);
        AuditLog::record('community.needs_review', $community);

        return $this->ok(new CommunityResource($community), 'انجمن برای بازبینی علامت‌گذاری شد.');
    }

    public function destroy(Community $community)
    {
        AuditLog::record('community.delete', $community);
        $community->delete();

        return $this->ok([], 'انجمن حذف شد.');
    }
}
