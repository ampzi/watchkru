<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userId = $request->user()?->id;
        $lastRead = $this->members
            ?->firstWhere('id', $userId)?->pivot?->last_read_message_id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'admin_note' => $this->admin_note,
            'members_count' => $this->members_count ?? $this->members?->count(),
            'unread_count' => $this->when(
                $userId !== null,
                fn () => $this->messages()
                    ->where('user_id', '!=', $userId)
                    ->when($lastRead, fn ($q) => $q->where('id', '>', $lastRead))
                    ->count()
            ),
            'is_owner' => $userId !== null && $this->created_by === $userId,
            'last_message' => $this->whenLoaded('latestMessage', fn () => $this->latestMessage ? [
                'body' => $this->latestMessage->body,
                'created_at' => $this->latestMessage->created_at->toIso8601String(),
                'user' => $this->latestMessage->user?->display_name,
            ] : null),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
