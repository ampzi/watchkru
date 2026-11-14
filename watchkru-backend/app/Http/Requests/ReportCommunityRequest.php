<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportCommunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_type' => ['required', Rule::in([
                'broken_link', 'inactive', 'wrong_description', 'wrong_media',
                'wrong_platform', 'spam', 'inappropriate', 'duplicate', 'other',
            ])],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
