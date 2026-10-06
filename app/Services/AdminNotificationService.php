<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminNotificationService
{
    /**
     * Send an in-app notification to all active admins.
     *
     * Notification failure must never break the main
     * business action that triggered the notification.
     */
    public static function send(
        string $title,
        string $message,
        ?string $url = null,
        string $category = 'general'
    ): void {
        try {
            User::query()
                ->where('role', 'admin')
                ->where('status', 'active')
                ->select('id', 'name', 'email', 'role', 'status')
                ->orderBy('id')
                ->chunkById(
                    100,
                    function ($admins) use (
                        $title,
                        $message,
                        $url,
                        $category
                    ) {
                        foreach ($admins as $admin) {
                            try {
                                $admin->notify(
                                    new AppNotification(
                                        $title,
                                        $message,
                                        $url,
                                        $category
                                    )
                                );
                            } catch (Throwable $exception) {
                                Log::warning(
                                    'Admin notification delivery failed.',
                                    [
                                        'admin_id' => $admin->id,
                                        'title' => $title,
                                        'category' => $category,
                                        'exception' => $exception->getMessage(),
                                    ]
                                );
                            }
                        }
                    }
                );
        } catch (Throwable $exception) {
            Log::warning(
                'Admin notification service failed.',
                [
                    'title' => $title,
                    'category' => $category,
                    'exception' => $exception->getMessage(),
                ]
            );
        }
    }
}