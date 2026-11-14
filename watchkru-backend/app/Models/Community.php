<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Community extends Model
{
    use HasFactory, SoftDeletes;

    // Verification status: distinct from activity status (Principle 2 —
    // never confuse "is this a real, well-formed listing" with "is it
    // currently active"). A verified community can still go inactive later.
    public const VERIFICATION_PENDING = 'pending';
    public const VERIFICATION_VERIFIED = 'verified';
    public const VERIFICATION_REJECTED = 'rejected';
    public const VERIFICATION_NEEDS_REVIEW = 'needs_review';

    public const ACTIVITY_ACTIVE = 'active';
    public const ACTIVITY_INACTIVE = 'inactive';
    public const ACTIVITY_UNKNOWN = 'unknown';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'external_url',
        'normalized_url',
        'platform_id',
        'community_category_id',
        'language',
        'member_count',
        'member_count_verified_at',
        'activity_status',
        'verification_status',
        'last_checked_at',
        'submission_source',
        'created_by',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'member_count_verified_at' => 'datetime',
            'last_checked_at' => 'datetime',
        ];
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function communityCategory(): BelongsTo
    {
        return $this->belongsTo(CommunityCategory::class);
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'community_media')->withTimestamps();
    }

    /** Convenience accessor for the first/primary related title. */
    public function primaryMedia()
    {
        return $this->media()->first();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(CommunitySubmission::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CommunityReport::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CommunityReview::class);
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', self::VERIFICATION_VERIFIED);
    }

    /**
     * Normalize a URL for duplicate detection: lower-case host, strip
     * trailing slash, drop query string/fragment and a leading "www.".
     */
    public static function normalizeUrl(string $url): string
    {
        $parts = parse_url(trim($url));
        $host = strtolower($parts['host'] ?? '');
        $host = preg_replace('/^www\./', '', $host);
        $path = rtrim($parts['path'] ?? '', '/');

        return $host.$path;
    }
}
