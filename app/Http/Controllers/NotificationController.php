<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = AppNotification::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('pages.notifications.index', [
            'title' => 'Notificaciones',
            'notifications' => $notifications,
        ]);
    }

    public function markRead(AppNotification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->markAsRead();

        return back();
    }

    public function markAllRead()
    {
        AppNotification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Notificaciones marcadas como leídas.');
    }
}
