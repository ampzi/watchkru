<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:80'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام گروه را وارد کنید.',
            'name.min' => 'نام گروه باید حداقل ۳ حرف باشد.',
            'name.max' => 'نام گروه حداکثر ۸۰ حرف است.',
            'description.max' => 'توضیح گروه حداکثر ۱۰۰۰ حرف است.',
        ];
    }
}
