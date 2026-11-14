<?php

namespace App\Console\Commands;

use App\Models\ChatGroup;
use App\Models\Community;
use App\Models\CommunityCategory;
use App\Models\CommunityReport;
use App\Models\CommunityReview;
use App\Models\CommunitySubmission;
use App\Models\Genre;
use App\Models\Media;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * GitHub Pages can only serve static files, so the Laravel backend cannot be
 * deployed there. This command snapshots everything the public site needs
 * (catalogue, communities, chat demo, demo users, admin counters) into one
 * JSON file that the frontend reads in "static mode".
 *
 * The output folder is `docs/` because GitHub Pages can only serve the
 * repository root or `/docs` from a branch.
 *
 * Usage: php artisan watchkru:export-static --path=../docs
 */
class ExportStaticSite extends Command
{
    protected $signature = 'watchkru:export-static {--path=../docs : Output directory for the static site}';

    protected $description = 'Export a static JSON snapshot of the catalogue for GitHub Pages.';

    public function handle(): int
    {
        $out = $this->resolvePath((string) $this->option('path'));
        File::ensureDirectoryExists($out.'/data');

        $media = Media::query()
            ->with('genres:id,name,slug')
            ->withCount('communities')
            ->orderByDesc('communities_count')
            ->orderByDesc('release_date')
            ->get()
            ->map(fn (Media $m) => [
                'id' => $m->id,
                'slug' => $m->slug,
                'title' => $m->title,
                'original_title' => $m->original_title,
                'alternative_titles' => $m->alternative_titles ?? [],
                'poster_path' => $m->poster_path,
                'backdrop_path' => $m->backdrop_path,
                'media_type' => $m->media_type,
                'release_year' => $m->release_year,
                'release_date' => $m->release_date?->toDateString(),
                'language' => $m->language,
                'status' => $m->status,
                'runtime' => $m->runtime,
                'number_of_seasons' => $m->number_of_seasons,
                'number_of_episodes' => $m->number_of_episodes,
                'director' => $m->director,
                'cast' => $m->cast ?? [],
                'overview' => $m->overview,
                'genres' => $m->genres->map(fn ($g) => [
                    'id' => $g->id,
                    'name' => $g->name,
                    'slug' => $g->slug,
                ])->values(),
                'communities_count' => $m->communities_count,
                'created_at' => $m->created_at?->toIso8601String(),
            ]);

        $communities = Community::query()
            ->with(['platform', 'communityCategory', 'media', 'reviews.user'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->verified()
            ->get()
            ->map(fn (Community $c) => [
                'id' => $c->id,
                'slug' => $c->slug,
                'name' => $c->name,
                'description' => $c->description,
                'platform' => $c->platform?->slug,
                'community_category' => $c->communityCategory ? [
                    'id' => $c->communityCategory->id,
                    'name' => $c->communityCategory->name,
                    'slug' => $c->communityCategory->slug,
                ] : null,
                'language' => $c->language,
                'external_url' => $c->external_url,
                'member_count' => $c->member_count,
                'member_count_verified_at' => $c->member_count_verified_at?->toDateString(),
                'activity_status' => $c->activity_status,
                'verification_status' => $c->verification_status,
                'last_checked_at' => $c->last_checked_at?->toDateString(),
                'media' => $c->media->first() ? [
                    'slug' => $c->media->first()->slug,
                    'title' => $c->media->first()->title,
                ] : null,
                'media_slugs' => $c->media->pluck('slug')->values(),
                'reviews_avg_rating' => $c->reviews_avg_rating ? round((float) $c->reviews_avg_rating, 1) : null,
                'reviews_count' => (int) $c->reviews_count,
                'reviews' => $c->reviews->map(fn (CommunityReview $r) => [
                    'id' => $r->id,
                    'rating' => $r->rating,
                    'content' => $r->content,
                    'user' => $r->user ? [
                        'display_name' => $r->user->display_name,
                        'username' => $r->user->username,
                    ] : null,
                    'created_at' => $r->created_at?->toDateString(),
                ])->values(),
                'created_at' => $c->created_at?->toDateString(),
            ]);

        $chatGroups = ChatGroup::query()
            ->with(['members:id,name,username', 'messages.user:id,name,username'])
            ->withCount('members')
            ->orderBy('id')
            ->get()
            ->map(fn (ChatGroup $g) => [
                'id' => $g->id,
                'name' => $g->name,
                'slug' => $g->slug,
                'description' => $g->description,
                'status' => $g->status,
                'created_by' => $g->created_by,
                'members_count' => $g->members_count,
                'members' => $g->members->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->display_name,
                    'username' => $u->username,
                ])->values(),
                'messages' => $g->messages->map(fn ($m) => [
                    'id' => $m->id,
                    'user_id' => $m->user_id,
                    'user' => $m->user ? [
                        'id' => $m->user->id,
                        'name' => $m->user->display_name,
                        'username' => $m->user->username,
                    ] : null,
                    'body' => $m->body,
                    'created_at' => $m->created_at?->toIso8601String(),
                ])->values(),
            ]);

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'stats' => [
                'total_media' => $media->count(),
                'total_series' => $media->where('media_type', Media::TYPE_SERIES)->count(),
                'total_movies' => $media->where('media_type', Media::TYPE_MOVIE)->count(),
                'total_communities' => $communities->count(),
                'total_users' => User::query()->count(),
                'needs_review' => Community::query()
                    ->where('verification_status', Community::VERIFICATION_NEEDS_REVIEW)->count(),
                'pending_submissions' => CommunitySubmission::query()
                    ->where('status', CommunitySubmission::STATUS_PENDING)->count(),
                'pending_reports' => CommunityReport::query()
                    ->where('status', CommunityReport::STATUS_OPEN)->count(),
            ],
            'genres' => Genre::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'platforms' => Platform::query()->get(['id', 'name', 'slug']),
            'community_categories' => CommunityCategory::query()->get(['id', 'name', 'slug']),
            'media' => $media,
            'communities' => $communities,
            'chat_groups' => $chatGroups,
            // Demo accounts for the static site; all use the password "password".
            'users' => User::query()
                ->orderBy('id')
                ->get(['id', 'name', 'username', 'email', 'role'])
                ->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'email' => $u->email,
                    'role' => $u->role,
                ]),
        ];

        File::put(
            $out.'/data/catalog.json',
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->info(sprintf(
            'Exported %d titles, %d communities, %d chat groups to %s',
            $media->count(),
            $communities->count(),
            $chatGroups->count(),
            $out.'/data/catalog.json'
        ));

        return self::SUCCESS;
    }

    protected function resolvePath(string $path): string
    {
        if (! str_starts_with($path, '/') && ! preg_match('/^[A-Za-z]:[\\\\\\/]/', $path)) {
            $path = base_path($path);
        }

        return rtrim(str_replace('\\', '/', $path), '/');
    }
}
