<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ChatAlert extends Notification
{
    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
        public string $color = 'info',
        public string $icon = 'heroicon-o-chat-bubble-left-right',
    ) {
    }

    public function via(object $notifiable): array
    {
        // Stored in the `notifications` table so the in-app bell works without
        // a queue worker or mail transport being configured.
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'color' => $this->color,
            'icon' => $this->icon,
        ];
    }
}
