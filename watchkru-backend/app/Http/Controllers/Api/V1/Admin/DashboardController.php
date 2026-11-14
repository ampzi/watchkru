<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\CommunityReport;
use App\Models\CommunitySubmission;
use App\Models\Media;
use App\Models\User;

class DashboardController extends Controller
{
    use ApiResponses;

    public function index()
    {
        return $this->ok([
            'total_movies' => Media::movies()->count(),
            'total_series' => Media::series()->count(),
            'total_communities' => Community::count(),
            'pending_submissions' => CommunitySubmission::where('status', CommunitySubmission::STATUS_PENDING)->count(),
            'needs_review' => Community::where('verification_status', Community::VERIFICATION_NEEDS_REVIEW)->count(),
            'total_users' => User::count(),
            'pending_reports' => CommunityReport::where('status', CommunityReport::STATUS_OPEN)->count(),
            'recent_communities' => Community::with('platform')->latest()->limit(5)->get(['id', 'name', 'slug', 'platform_id', 'created_at']),
        ]);
    }
}
