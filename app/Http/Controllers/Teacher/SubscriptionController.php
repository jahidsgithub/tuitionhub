<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $plans = SubscriptionPlan::query()
            ->where(
                'status',
                true
            )
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        $activeSubscription =
            $user->activeSubscription();

        $pendingSubscription =
            Subscription::query()
                ->with([
                    'plan',
                    'payments',
                ])
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'status',
                    'pending'
                )
                ->latest()
                ->first();

        $subscriptionHistory =
            Subscription::query()
                ->with([
                    'plan',
                    'payments',
                ])
                ->where(
                    'user_id',
                    $user->id
                )
                ->latest()
                ->paginate(15);

        return view(
            'teacher.subscription.index',
            compact(
                'plans',
                'activeSubscription',
                'pendingSubscription',
                'subscriptionHistory'
            )
        );
    }

    public function subscribe(
        SubscriptionPlan $plan
    ): RedirectResponse {
        try {
            $result = DB::transaction(
                function () use ($plan) {

                    $lockedUser =
                        User::query()
                            ->lockForUpdate()
                            ->findOrFail(
                                auth()->id()
                            );

                    if (! $lockedUser->isTeacher()) {
                        abort(403);
                    }

                    if (! $lockedUser->isActive()) {
                        return [
                            'success' => false,
                            'message' =>
                                'Your account is not currently active.',
                        ];
                    }

                    $lockedPlan =
                        SubscriptionPlan::query()
                            ->lockForUpdate()
                            ->findOrFail(
                                $plan->id
                            );

                    if (! $lockedPlan->status) {
                        return [
                            'success' => false,
                            'message' =>
                                'This subscription plan is currently unavailable.',
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Existing Pending
                    |--------------------------------------------------------------------------
                    */

                    $pendingSubscription =
                        Subscription::query()
                            ->where(
                                'user_id',
                                $lockedUser->id
                            )
                            ->where(
                                'status',
                                'pending'
                            )
                            ->lockForUpdate()
                            ->latest('id')
                            ->first();

                    if ($pendingSubscription) {
                        if (
                            (float) $pendingSubscription->amount > 0
                        ) {
                            return [
                                'success' => false,
                                'redirect_to_payment' => true,
                                'subscription_id' =>
                                    $pendingSubscription->id,
                                'message' =>
                                    'You already have a subscription waiting for payment.',
                            ];
                        }

                        return [
                            'success' => false,
                            'message' =>
                                'You already have a pending subscription.',
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Expire Stale Active
                    |--------------------------------------------------------------------------
                    */

                    $expiredSubscriptions =
                        Subscription::query()
                            ->where(
                                'user_id',
                                $lockedUser->id
                            )
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
                            ->lockForUpdate()
                            ->get();

                    foreach (
                        $expiredSubscriptions
                        as $expiredSubscription
                    ) {
                        $expiredSubscription->update([
                            'status' => 'expired',
                            'pending_user_id' => null,
                        ]);
                    }

                    $activeSubscription =
                        Subscription::query()
                            ->with('plan')
                            ->where(
                                'user_id',
                                $lockedUser->id
                            )
                            ->where(
                                'status',
                                'active'
                            )
                            ->where(
                                function ($query) {
                                    $query
                                        ->whereNull(
                                            'expires_at'
                                        )
                                        ->orWhere(
                                            'expires_at',
                                            '>',
                                            now()
                                        );
                                }
                            )
                            ->lockForUpdate()
                            ->latest('expires_at')
                            ->first();

                    $snapshot = [
                        'plan_name_snapshot' =>
                            $lockedPlan->name,

                        'duration_days_snapshot' =>
                            $lockedPlan->duration_days,

                        'application_limit_snapshot' =>
                            $lockedPlan->application_limit,
                    ];

                    /*
                    |--------------------------------------------------------------------------
                    | Free Plan
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (float) $lockedPlan->price <= 0
                    ) {
                        if ($activeSubscription) {
                            return [
                                'success' => false,
                                'message' =>
                                    'You already have an active subscription. The free plan cannot replace an active subscription.',
                            ];
                        }

                        $usedFreePlan =
                            Subscription::query()
                                ->where(
                                    'user_id',
                                    $lockedUser->id
                                )
                                ->where(
                                    'subscription_plan_id',
                                    $lockedPlan->id
                                )
                                ->whereIn(
                                    'status',
                                    [
                                        'active',
                                        'expired',
                                        'cancelled',
                                    ]
                                )
                                ->lockForUpdate()
                                ->exists();

                        if ($usedFreePlan) {
                            return [
                                'success' => false,
                                'message' =>
                                    'The free subscription plan can only be activated once.',
                            ];
                        }

                        $startsAt = now();

                        $expiresAt =
                            $lockedPlan->duration_days
                                ? $startsAt
                                    ->copy()
                                    ->addDays(
                                        $lockedPlan
                                            ->duration_days
                                    )
                                : null;

                        $subscription =
                            Subscription::create([
                                'user_id' =>
                                    $lockedUser->id,

                                'subscription_plan_id' =>
                                    $lockedPlan->id,

                                ...$snapshot,

                                'amount' => 0,

                                'starts_at' =>
                                    $startsAt,

                                'expires_at' =>
                                    $expiresAt,

                                'status' =>
                                    'active',

                                'applications_used' =>
                                    0,

                                'pending_user_id' =>
                                    null,
                            ]);

                        return [
                            'success' => true,
                            'type' => 'free_activation',
                            'subscription_id' =>
                                $subscription->id,
                            'message' =>
                                'Free subscription activated successfully.',
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Renewal / Upgrade
                    |--------------------------------------------------------------------------
                    */

                    if ($activeSubscription) {
                        if (
                            $activeSubscription
                                ->subscription_plan_id ===
                            $lockedPlan->id
                        ) {
                            $subscription =
                                Subscription::create([
                                    'user_id' =>
                                        $lockedUser->id,

                                    'subscription_plan_id' =>
                                        $lockedPlan->id,

                                    ...$snapshot,

                                    'amount' =>
                                        $lockedPlan->price,

                                    'starts_at' =>
                                        null,

                                    'expires_at' =>
                                        null,

                                    'status' =>
                                        'pending',

                                    'applications_used' =>
                                        0,

                                    'pending_user_id' =>
                                        $lockedUser->id,
                                ]);

                            return [
                                'success' => true,
                                'type' => 'renewal',
                                'subscription_id' =>
                                    $subscription->id,
                                'message' =>
                                    'Renewal request created. Please complete payment.',
                            ];
                        }

                        $currentPrice =
                            (float)
                            $activeSubscription->amount;

                        $newPrice =
                            (float)
                            $lockedPlan->price;

                        if (
                            $newPrice <=
                            $currentPrice
                        ) {
                            return [
                                'success' => false,
                                'message' =>
                                    'You cannot downgrade while another subscription is active.',
                            ];
                        }

                        $subscription =
                            Subscription::create([
                                'user_id' =>
                                    $lockedUser->id,

                                'subscription_plan_id' =>
                                    $lockedPlan->id,

                                ...$snapshot,

                                'amount' =>
                                    $lockedPlan->price,

                                'starts_at' =>
                                    null,

                                'expires_at' =>
                                    null,

                                'status' =>
                                    'pending',

                                'applications_used' =>
                                    0,

                                'pending_user_id' =>
                                    $lockedUser->id,
                            ]);

                        return [
                            'success' => true,
                            'type' => 'upgrade',
                            'subscription_id' =>
                                $subscription->id,
                            'message' =>
                                'Upgrade request created. Please complete payment.',
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | New Paid Subscription
                    |--------------------------------------------------------------------------
                    */

                    $subscription =
                        Subscription::create([
                            'user_id' =>
                                $lockedUser->id,

                            'subscription_plan_id' =>
                                $lockedPlan->id,

                            ...$snapshot,

                            'amount' =>
                                $lockedPlan->price,

                            'starts_at' =>
                                null,

                            'expires_at' =>
                                null,

                            'status' =>
                                'pending',

                            'applications_used' =>
                                0,

                            'pending_user_id' =>
                                $lockedUser->id,
                        ]);

                    return [
                        'success' => true,
                        'type' => 'new',
                        'subscription_id' =>
                            $subscription->id,
                        'message' =>
                            'Subscription request created. Please complete payment.',
                    ];
                },
                3
            );
        } catch (QueryException $e) {
            if (
                $this->isUniqueConstraintViolation(
                    $e
                )
            ) {
                $pendingSubscription =
                    Subscription::query()
                        ->where(
                            'user_id',
                            auth()->id()
                        )
                        ->where(
                            'status',
                            'pending'
                        )
                        ->latest()
                        ->first();

                if ($pendingSubscription) {
                    return redirect()
                        ->route(
                            'teacher.payment.create',
                            $pendingSubscription
                        )
                        ->with(
                            'error',
                            'You already have a pending subscription.'
                        );
                }

                return back()->with(
                    'error',
                    'A pending subscription already exists.'
                );
            }

            throw $e;
        }

        if (
            ! $result['success'] &&
            ! empty(
                $result['redirect_to_payment']
            )
        ) {
            return redirect()
                ->route(
                    'teacher.payment.create',
                    $result['subscription_id']
                )
                ->with(
                    'error',
                    $result['message']
                );
        }

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        if (
            $result['type'] ===
            'free_activation'
        ) {
            return redirect()
                ->route(
                    'teacher.subscription.index'
                )
                ->with(
                    'success',
                    $result['message']
                );
        }

        return redirect()
            ->route(
                'teacher.payment.create',
                $result['subscription_id']
            )
            ->with(
                'success',
                $result['message']
            );
    }

    private function isUniqueConstraintViolation(
        QueryException $exception
    ): bool {
        $sqlState =
            $exception->errorInfo[0]
            ?? null;

        $driverCode =
            $exception->errorInfo[1]
            ?? null;

        return in_array(
            (string) $sqlState,
            [
                '23000',
                '23505',
            ],
            true
        ) || in_array(
            (int) $driverCode,
            [
                1062,
                19,
            ],
            true
        );
    }
}