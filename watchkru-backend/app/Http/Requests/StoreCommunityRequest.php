<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by auth:sanctum middleware on the route
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'platform' => ['required', Rule::in([
                'telegram_group', 'telegram_channel', 'discord_server',
                'instagram_page', 'reddit_community', 'whatsapp_group',
                'website_forum', 'other',
            ])],
            // external_url validated as http/https only — see security notes
            // in the README; dangerous schemes (javascript:, data:, etc.)
            // are rejected before the row ever reaches the DB.
            'external_url' => ['required', 'url:http,https', 'max:2048'],
            'media_id' => ['required', 'exists:media,id'],
            'community_category_id' => ['required', 'exists:community_categories,id'],
            'language' => ['nullable', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:1000'],
            'member_count' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
