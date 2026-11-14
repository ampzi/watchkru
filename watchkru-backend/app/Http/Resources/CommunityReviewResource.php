<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunityReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'content' => $this->content,
            'user' => [
                'username' => $this->user?->username,
                'display_name' => $this->user?->display_name,
            ],
            'created_at' => $this->created_at?->toDateString(),
        ];
    }
}
