<?php

namespace App\Notifications;

use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

/**
 * Stored in the `notifications` table and rendered by Filament's bell.
 * Deliberately not queued: the panel shows it immediately without a worker.
 */
class AdminAlert extends Notification
{
    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
        public string $color = 'warning',
        public string $icon = 'heroicon-o-bell-alert',
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $notification = FilamentNotification::make()
            ->title($this->title)
            ->body($this->body)
            ->icon($this->icon)
            ->color($this->color);

        if ($this->url) {
            $notification->actions([
                Action::make('view')
                    ->label('مشاهده')
                    ->url($this->url)
                    ->button()
                    ->markAsRead(),
            ]);
        }

        return $notification->getDatabaseMessage();
    }
}
