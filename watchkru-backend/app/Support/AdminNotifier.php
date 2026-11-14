<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\AdminAlert;
use Illuminate\Support\Facades\Notification;

class AdminNotifier
{
    public static function send(string $title, string $body, ?string $url = null, string $color = 'warning', string $icon = 'heroicon-o-bell-alert'): void
    {
        $admins = User::query()
            ->whereIn('role', [
                User::ROLE_MODERATOR,
                User::ROLE_ADMIN,
                User::ROLE_SUPER_ADMIN,
            ])
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new AdminAlert($title, $body, $url, $color, $icon));
    }
}
