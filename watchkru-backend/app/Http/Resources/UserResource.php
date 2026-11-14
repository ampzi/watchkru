<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'bio' => $this->bio,
            'role' => $this->role,
            'joined_at' => $this->created_at?->toDateString(),
        ];
    }
}
