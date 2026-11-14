<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'min:3', 'max:150'],
            'body' => ['required', 'string', 'min:10', 'max:4000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام خود را وارد کنید.',
            'email.required' => 'ایمیل خود را وارد کنید.',
            'email.email' => 'ایمیل معتبر نیست.',
            'subject.required' => 'موضوع پیام را وارد کنید.',
            'subject.min' => 'موضوع باید حداقل ۳ حرف باشد.',
            'body.required' => 'متن پیام را بنویسید.',
            'body.min' => 'متن پیام باید حداقل ۱۰ حرف باشد.',
            'body.max' => 'متن پیام حداکثر ۴۰۰۰ حرف است.',
        ];
    }
}
