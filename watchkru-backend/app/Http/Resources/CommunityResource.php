<?php

namespace App\Http\Resources;

use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $media = $this->relationLoaded('media') ? $this->media->first() : null;

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'platform' => $this->platform?->slug,
            'community_category' => $this->whenLoaded('communityCategory', fn () => [
                'id' => $this->communityCategory->id,
                'name' => $this->communityCategory->name,
                'slug' => $this->communityCategory->slug,
            ]),
            'language' => $this->language,
            'external_url' => $this->external_url,
            // Principle 3: never surface a member count we have not
            // actually verified — unverified listings show null, and the
            // frontend renders that as "نامشخص" rather than inventing a figure.
            'member_count' => $this->verification_status === Community::VERIFICATION_VERIFIED
                ? $this->member_count
                : null,
            'member_count_verified_at' => $this->member_count_verified_at?->toDateString(),
            'activity_status' => $this->activity_status,
            'verification_status' => $this->verification_status,
            'last_checked_at' => $this->last_checked_at?->toDateString(),
            'media' => $media ? [
                'slug' => $media->slug,
                'title' => $media->title,
            ] : null,
            'reviews_avg_rating' => $this->when(isset($this->reviews_avg_rating), fn () => round((float) $this->reviews_avg_rating, 1)),
            'reviews_count' => $this->when(isset($this->reviews_count), fn () => (int) $this->reviews_count),
            'reviews' => CommunityReviewResource::collection($this->whenLoaded('reviews')),
            'created_at' => $this->created_at?->toDateString(),
        ];
    }
}
