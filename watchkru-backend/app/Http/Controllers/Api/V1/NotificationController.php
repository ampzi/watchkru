<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $user = $request->user();

        $items = $user->notifications()
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'اعلان',
                'body' => $n->data['body'] ?? '',
                'url' => $n->data['url'] ?? null,
                'color' => $n->data['color'] ?? 'info',
                'icon' => $n->data['icon'] ?? null,
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at->toIso8601String(),
            ]);

        return $this->ok([
            'items' => $items,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return $this->ok(['unread_count' => 0], 'همه اعلان‌ها خوانده شدند.');
    }
}
