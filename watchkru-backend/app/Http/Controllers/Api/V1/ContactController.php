<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Models\ContactMessage;
use App\Support\AdminNotifier;

class ContactController extends Controller
{
    use ApiResponses;

    public function store(StoreContactRequest $request)
    {
        $message = ContactMessage::create([
            ...$request->validated(),
            'user_id' => $request->user()?->id,
            'status' => ContactMessage::STATUS_NEW,
        ]);

        AdminNotifier::send(
            'پیام تازه از فرم تماس',
            "{$message->name} ({$message->email}): {$message->subject}",
            url('/admin/contact-messages'),
            'info',
            'heroicon-o-envelope',
        );

        return $this->ok(
            ['id' => $message->id],
            'پیام شما ثبت شد. تیم واچ‌کرو در اولین فرصت پاسخ می‌دهد.',
            201
        );
    }
}
