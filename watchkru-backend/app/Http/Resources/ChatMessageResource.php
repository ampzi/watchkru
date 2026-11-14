<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'created_at' => $this->created_at->toIso8601String(),
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->display_name,
                'username' => $this->user?->username,
                'avatar' => $this->user?->avatar,
            ],
            'mine' => $request->user()?->id === $this->user_id,
        ];
    }
}
