<?php

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Default Inspire Command
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(
        Inspiring::quote()
    );
})->purpose(
    'Display an inspiring quote'
);

/*
|--------------------------------------------------------------------------
| Expire Active Subscriptions
|--------------------------------------------------------------------------
*/

Artisan::command('subscriptions:expire', function () {

    $expiredCount = 0;

    Subscription::query()
        ->where(
            'status',
            'active'
        )
        ->whereNotNull(
            'expires_at'
        )
        ->where(
            'expires_at',
            '<=',
            now()
        )
        ->orderBy('id')
        ->chunkById(
            100,
            function (
                $subscriptions
            ) use (
                &$expiredCount
            ) {
                foreach (
                    $subscriptions
                    as $subscription
                ) {
                    $notificationData =
                        DB::transaction(
                            function () use (
                                $subscription,
                                &$expiredCount
                            ) {
                                $lockedSubscription =
                                    Subscription::query()
                                        ->with('user')
                                        ->lockForUpdate()
                                        ->find(
                                            $subscription->id
                                        );

                                if (
                                    ! $lockedSubscription
                                ) {
                                    return null;
                                }

                                /*
                                |--------------------------------------------------------------------------
                                | Re-check Under Lock
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    $lockedSubscription
                                        ->status !==
                                        'active'
                                ) {
                                    return null;
                                }

                                if (
                                    ! $lockedSubscription
                                        ->expires_at
                                ) {
                                    return null;
                                }

                                if (
                                    $lockedSubscription
                                        ->expires_at
                                        ->isFuture()
                                ) {
                                    return null;
                                }

                                /*
                                |--------------------------------------------------------------------------
                                | Expire Subscription
                                |--------------------------------------------------------------------------
                                */

                                $lockedSubscription
                                    ->update([
                                        'status' =>
                                            'expired',

                                        'pending_user_id' =>
                                            null,
                                    ]);

                                $expiredCount++;

                                if (
                                    ! $lockedSubscription
                                        ->user
                                ) {
                                    return null;
                                }

                                return [
                                    'user_id' =>
                                        $lockedSubscription
                                            ->user
                                            ->id,

                                    'plan_name' =>
                                        $lockedSubscription
                                            ->snapshotPlanName()
                                        ?: 'subscription',
                                ];
                            }
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Notify After Commit
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! $notificationData
                    ) {
                        continue;
                    }

                    $user = User::query()
                        ->find(
                            $notificationData[
                                'user_id'
                            ]
                        );

                    if (! $user) {
                        continue;
                    }

                    $user->notify(
                        new AppNotification(
                            'Subscription Expired',

                            'Your '
                            .$notificationData[
                                'plan_name'
                            ]
                            .' subscription has expired. Renew your subscription to continue applying for tuition.',

                            route(
                                'teacher.subscription.index'
                            ),

                            'subscription'
                        )
                    );
                }
            }
        );

    $this->info(
        $expiredCount
        .' subscription(s) marked as expired.'
    );

})->purpose(
    'Mark expired subscriptions as expired and notify teachers.'
);

/*
|--------------------------------------------------------------------------
| Clean Stale Pending Checkouts
|--------------------------------------------------------------------------
|
| A paid subscription may remain pending when the teacher starts checkout
| but never submits/completes payment.
|
| Pending subscriptions older than 24 hours are cancelled.
| Their pending payments are marked failed.
|
| Both DB uniqueness guard columns are released.
|
*/

Artisan::command('subscriptions:cleanup-pending', function () {

    $cancelledSubscriptions = 0;
    $failedPayments = 0;

    $cutoff = now()->subHours(24);

    Subscription::query()
        ->where(
            'status',
            'pending'
        )
        ->where(
            'created_at',
            '<=',
            $cutoff
        )
        ->orderBy('id')
        ->chunkById(
            100,
            function (
                $subscriptions
            ) use (
                &$cancelledSubscriptions,
                &$failedPayments,
                $cutoff
            ) {
                foreach (
                    $subscriptions
                    as $subscription
                ) {
                    $notificationData =
                        DB::transaction(
                            function () use (
                                $subscription,
                                &$cancelledSubscriptions,
                                &$failedPayments,
                                $cutoff
                            ) {
                                /*
                                |--------------------------------------------------------------------------
                                | Lock Subscription
                                |--------------------------------------------------------------------------
                                */

                                $lockedSubscription =
                                    Subscription::query()
                                        ->with('user')
                                        ->lockForUpdate()
                                        ->find(
                                            $subscription->id
                                        );

                                if (
                                    ! $lockedSubscription
                                ) {
                                    return null;
                                }

                                /*
                                |--------------------------------------------------------------------------
                                | Re-check Pending State
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    $lockedSubscription
                                        ->status !==
                                        'pending'
                                ) {
                                    return null;
                                }

                                /*
                                |--------------------------------------------------------------------------
                                | Re-check Age
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    ! $lockedSubscription
                                        ->created_at ||
                                    $lockedSubscription
                                        ->created_at
                                        ->gt($cutoff)
                                ) {
                                    return null;
                                }

                                /*
                                |--------------------------------------------------------------------------
                                | Lock Pending Payments
                                |--------------------------------------------------------------------------
                                */

                                $pendingPayments =
                                    $lockedSubscription
                                        ->payments()
                                        ->where(
                                            'status',
                                            'pending'
                                        )
                                        ->orderBy('id')
                                        ->lockForUpdate()
                                        ->get();

                                /*
                                |--------------------------------------------------------------------------
                                | Fail Stale Payments
                                |--------------------------------------------------------------------------
                                */

                                foreach (
                                    $pendingPayments
                                    as $payment
                                ) {
                                    $payment->update([
                                        'status' =>
                                            'failed',

                                        'pending_subscription_id' =>
                                            null,
                                    ]);

                                    $failedPayments++;
                                }

                                /*
                                |--------------------------------------------------------------------------
                                | Cancel Pending Subscription
                                |--------------------------------------------------------------------------
                                */

                                $lockedSubscription
                                    ->update([
                                        'status' =>
                                            'cancelled',

                                        'pending_user_id' =>
                                            null,
                                    ]);

                                $cancelledSubscriptions++;

                                if (
                                    ! $lockedSubscription
                                        ->user
                                ) {
                                    return null;
                                }

                                return [
                                    'user_id' =>
                                        $lockedSubscription
                                            ->user
                                            ->id,

                                    'plan_name' =>
                                        $lockedSubscription
                                            ->snapshotPlanName()
                                        ?: 'subscription',
                                ];
                            }
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Notify After Commit
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! $notificationData
                    ) {
                        continue;
                    }

                    $user = User::query()
                        ->find(
                            $notificationData[
                                'user_id'
                            ]
                        );

                    if (! $user) {
                        continue;
                    }

                    $user->notify(
                        new AppNotification(
                            'Pending Subscription Expired',

                            'Your pending '
                            .$notificationData[
                                'plan_name'
                            ]
                            .' subscription checkout expired because payment was not completed within 24 hours. You can start a new subscription checkout anytime.',

                            route(
                                'teacher.subscription.index'
                            ),

                            'subscription'
                        )
                    );
                }
            }
        );

    $this->info(
        $cancelledSubscriptions
        .' stale pending subscription(s) cancelled.'
    );

    $this->info(
        $failedPayments
        .' stale pending payment(s) marked as failed.'
    );

})->purpose(
    'Cancel subscription checkouts that have remained pending for more than 24 hours.'
);

/*
|--------------------------------------------------------------------------
| Scheduler
|--------------------------------------------------------------------------
*/

Schedule::command(
    'subscriptions:expire'
)
    ->hourly()
    ->withoutOverlapping();

Schedule::command(
    'subscriptions:cleanup-pending'
)
    ->hourly()
    ->withoutOverlapping();