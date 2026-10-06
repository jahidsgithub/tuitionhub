<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Show logged-in user's notifications.
     */
    public function index(): View
    {
        $notifications = auth()
            ->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        $unreadCount = auth()
            ->user()
            ->unreadNotifications()
            ->count();

        return view(
            'notifications.index',
            compact(
                'notifications',
                'unreadCount'
            )
        );
    }

    /**
     * Mark one notification as read.
     */
    public function markRead(
        string $notification
    ): RedirectResponse {

        $item = auth()
            ->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if ($item->read_at === null) {
            $item->markAsRead();
        }

        $url = data_get(
            $item->data,
            'url'
        );

        if ($url) {
            return redirect($url);
        }

        return back();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): RedirectResponse
    {
        auth()
            ->user()
            ->unreadNotifications
            ->markAsRead();

        return back()->with(
            'success',
            'All notifications marked as read.'
        );
    }

    /**
     * Delete one notification.
     */
    public function destroy(
        string $notification
    ): RedirectResponse {

        $item = auth()
            ->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $item->delete();

        return back()->with(
            'success',
            'Notification deleted.'
        );
    }
}