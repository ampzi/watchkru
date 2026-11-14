<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportCommunityRequest;
use App\Http\Requests\StoreCommunityRequest;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\CommunityResource;
use App\Models\Community;
use App\Models\CommunityReport;
use App\Models\CommunityReview;
use App\Models\CommunitySubmission;
use App\Models\Media;
use App\Models\Platform;
use App\Services\DuplicateCommunityDetector;
use App\Support\AdminNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommunityController extends Controller
{
    use ApiResponses;

    public function __construct(protected DuplicateCommunityDetector $duplicates)
    {
    }

    public function index(Request $request)
    {
        $query = Community::query()
            ->with(['platform', 'communityCategory', 'media'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        // Public listings only ever show verified, non-rejected entries
        // unless a specific status is explicitly requested (admin routes
        // use a separate controller with no such restriction).
        if ($status = $request->get('verification_status')) {
            $query->where('verification_status', $status);
        } else {
            $query->where('verification_status', Community::VERIFICATION_VERIFIED);
        }

        if ($mediaSlug = $request->get('media')) {
            $query->whereHas('media', fn ($q) => $q->where('slug', $mediaSlug));
        }

        if ($platform = $request->get('platform')) {
            $query->whereHas('platform', fn ($q) => $q->where('slug', $platform));
        }

        if ($language = $request->get('language')) {
            $query->where('language', $language);
        }

        if ($activity = $request->get('activity_status')) {
            $query->where('activity_status', $activity);
        }

        if ($q = $request->get('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%");
            });
        }

        $query = match ($request->get('sort')) {
            'newest' => $query->orderByDesc('created_at'),
            'trending' => $query->orderByDesc('reviews_count')->orderByDesc('member_count'),
            default => $query->orderByDesc('created_at'),
        };

        return $this->paginated($query->paginate(min((int) $request->get('per_page', 20), 50)), CommunityResource::class);
    }

    public function show(Community $community)
    {
        $community->load(['platform', 'communityCategory', 'media', 'reviews.user'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return $this->ok(new CommunityResource($community));
    }

    public function store(StoreCommunityRequest $request)
    {
        $existing = $this->duplicates->findByUrl($request->external_url);

        if ($existing) {
            return $this->fail('این لینک قبلاً در واچ‌کرو ثبت شده است.', 422, [
                'external_url' => ['لینک تکراری است.'],
                'existing_community_slug' => $existing->slug,
            ]);
        }

        $media = Media::findOrFail($request->media_id);
        $similar = $this->duplicates->findSimilarForMedia($request->name, $media->id);

        $community = DB::transaction(function () use ($request, $media) {
            $community = Community::create([
                'name' => $request->name,
                'slug' => $this->uniqueSlug($request->name),
                'description' => $request->description,
                'external_url' => $request->external_url,
                'normalized_url' => Community::normalizeUrl($request->external_url),
                'platform_id' => Platform::where('slug', $request->platform)->firstOrFail()->id,
                'community_category_id' => $request->community_category_id,
                'language' => $request->language ?? 'fa',
                'member_count' => $request->member_count,
                'activity_status' => Community::ACTIVITY_UNKNOWN,
                'verification_status' => Community::VERIFICATION_PENDING,
                'submission_source' => 'user',
                'created_by' => $request->user()->id,
            ]);

            $community->media()->attach($media->id);

            CommunitySubmission::create([
                'user_id' => $request->user()->id,
                'community_id' => $community->id,
                'media_id' => $media->id,
                'submission_data' => $request->validated(),
                'status' => CommunitySubmission::STATUS_PENDING,
            ]);

            AdminNotifier::send(
                'انجمن تازه در انتظار بررسی',
                "«{$community->name}» برای «{$media->title}» ثبت شد و منتظر تأیید است.",
                url('/admin/communities/'.$community->id.'/edit'),
                'warning',
                'heroicon-o-inbox-arrow-down',
            );

            return $community;
        });

        return $this->ok([
            'community' => new CommunityResource($community->load(['platform', 'communityCategory', 'media'])),
            'similar_warning' => $similar->isNotEmpty()
                ? 'انجمن‌های مشابهی برای این عنوان ثبت شده‌اند؛ تیم بررسی این مورد را نیز در نظر می‌گیرد.'
                : null,
        ], 'انجمن شما ثبت شد و در انتظار بررسی است.', 201);
    }

    public function report(ReportCommunityRequest $request, Community $community)
    {
        CommunityReport::create([
            'community_id' => $community->id,
            'user_id' => $request->user()?->id,
            'report_type' => $request->report_type,
            'description' => $request->description,
            'status' => CommunityReport::STATUS_OPEN,
        ]);

        AdminNotifier::send(
            'گزارش تازه برای یک انجمن',
            "برای «{$community->name}» گزارش «{$request->report_type}» ثبت شد.",
            url('/admin/community-reports/'.$community->reports()->latest('id')->value('id').'/edit'),
            'danger',
            'heroicon-o-flag',
        );

        return $this->ok([], 'گزارش شما ثبت شد. سپاسگزاریم.', 201);
    }

    public function storeReview(StoreReviewRequest $request, Community $community)
    {
        $existing = CommunityReview::where('community_id', $community->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return $this->fail('شما قبلاً برای این انجمن نظر ثبت کرده‌اید.', 422);
        }

        $review = CommunityReview::create([
            'community_id' => $community->id,
            'user_id' => $request->user()->id,
            'rating' => $request->rating,
            'content' => $request->content,
        ]);

        return $this->ok($review, 'نظر شما ثبت شد.', 201);
    }

    public function updateReview(StoreReviewRequest $request, Community $community)
    {
        $review = CommunityReview::where('community_id', $community->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $review->update($request->validated());

        return $this->ok($review, 'نظر شما به‌روزرسانی شد.');
    }

    public function destroyReview(Request $request, Community $community)
    {
        CommunityReview::where('community_id', $community->id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return $this->ok([], 'نظر شما حذف شد.');
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: Str::random(8);
        $slug = $base;
        $i = 1;

        while (Community::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
