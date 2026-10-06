<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserNotificationService
{
    public static function send(
        ?User $user,
        string $title,
        string $message,
        ?string $url = null,
        string $category = 'general'
    ): void {
        if (! $user) {
            return;
        }

        try {
            $user->notify(
                new AppNotification(
                    $title,
                    $message,
                    $url,
                    $category
                )
            );
        } catch (Throwable $exception) {
            Log::warning(
                'User notification delivery failed.',
                [
                    'user_id' => $user->id,
                    'title' => $title,
                    'category' => $category,
                    'exception' => $exception->getMessage(),
                ]
            );
        }
    }
}