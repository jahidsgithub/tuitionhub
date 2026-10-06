<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(
        Request $request
    ): View {
        $query = Payment::query()
            ->with([
                'user',
                'subscription.plan',
            ]);

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('search')) {
            $search = trim(
                $request->search
            );

            $query->where(
                function ($q) use ($search) {
                    $q->where(
                        'transaction_id',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhereHas(
                            'user',
                            function ($userQuery) use ($search) {
                                $userQuery
                                    ->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'email',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'phone',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }

        $payments = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.payments.index',
            compact('payments')
        );
    }

    public function approve(
        Payment $payment
    ): RedirectResponse {
        $result = DB::transaction(
            function () use ($payment) {
                $lockedPayment = Payment::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $payment->id
                    );

                if (
                    $lockedPayment->status !==
                    'pending'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'Only pending payments can be approved.',
                    ];
                }

                if (
                    ! $lockedPayment
                        ->subscription_id
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'No subscription is linked to this payment.',
                    ];
                }

                $subscription = Subscription::query()
                    ->with([
                        'plan',
                        'user',
                    ])
                    ->lockForUpdate()
                    ->findOrFail(
                        $lockedPayment
                            ->subscription_id
                    );

                if (
                    $subscription->status !==
                    'pending'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'The linked subscription is no longer pending.',
                    ];
                }

                $durationDays =
                    $subscription
                        ->snapshotDurationDays();

                $activeSubscription =
                    Subscription::query()
                        ->where(
                            'user_id',
                            $subscription->user_id
                        )
                        ->where(
                            'status',
                            'active'
                        )
                        ->where(
                            'id',
                            '!=',
                            $subscription->id
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
                        ->latest(
                            'expires_at'
                        )
                        ->first();

                $actionType = 'activated';

                $startsAt = now();

                $expiresAt = $durationDays
                    ? $startsAt
                        ->copy()
                        ->addDays(
                            $durationDays
                        )
                    : null;

                if ($activeSubscription) {
                    if (
                        $activeSubscription
                            ->subscription_plan_id ===
                        $subscription
                            ->subscription_plan_id
                    ) {
                        $actionType =
                            'renewed';

                        $baseDate =
                            $activeSubscription
                                ->expires_at &&
                            $activeSubscription
                                ->expires_at
                                ->isFuture()
                                ? $activeSubscription
                                    ->expires_at
                                    ->copy()
                                : now();

                        $expiresAt =
                            $durationDays
                                ? $baseDate
                                    ->addDays(
                                        $durationDays
                                    )
                                : null;
                    } else {
                        $actionType =
                            'upgraded';

                        $expiresAt =
                            $durationDays
                                ? now()
                                    ->addDays(
                                        $durationDays
                                    )
                                : null;
                    }

                    $activeSubscription
                        ->update([
                            'status' =>
                                'cancelled',

                            'pending_user_id' =>
                                null,
                        ]);
                }

                Subscription::query()
                    ->where(
                        'user_id',
                        $subscription->user_id
                    )
                    ->where(
                        'status',
                        'active'
                    )
                    ->where(
                        'id',
                        '!=',
                        $subscription->id
                    )
                    ->update([
                        'status' =>
                            'cancelled',

                        'pending_user_id' =>
                            null,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Activate Pending Subscription
                |--------------------------------------------------------------------------
                */

                $subscription->update([
                    'status' =>
                        'active',

                    'starts_at' =>
                        $startsAt,

                    'expires_at' =>
                        $expiresAt,

                    'applications_used' =>
                        0,

                    /*
                    | It is no longer pending,
                    | so release DB unique guard.
                    */
                    'pending_user_id' =>
                        null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Mark Payment Paid + Release Guard
                |--------------------------------------------------------------------------
                */

                $lockedPayment->update([
                    'status' =>
                        'paid',

                    'paid_at' =>
                        now(),

                    'pending_subscription_id' =>
                        null,
                ]);

                return [
                    'success' => true,

                    'subscription_id' =>
                        $subscription->id,

                    'action_type' =>
                        $actionType,
                ];
            },
            3
        );

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        $subscription =
            Subscription::query()
                ->with([
                    'user',
                    'plan',
                ])
                ->find(
                    $result[
                        'subscription_id'
                    ]
                );

        if ($subscription?->user) {
            $title = match (
                $result['action_type']
            ) {
                'renewed' =>
                    'Subscription Renewed',

                'upgraded' =>
                    'Subscription Upgraded',

                default =>
                    'Subscription Activated',
            };

            UserNotificationService::send(
                $subscription->user,
                $title,
                $subscription
                    ->snapshotPlanName()
                    .' subscription has been '
                    .$result['action_type']
                    .'.',
                route(
                    'teacher.subscription.index'
                ),
                'subscription'
            );
        }

        return back()->with(
            'success',
            'Payment approved and subscription '
                .$result['action_type']
                .' successfully.'
        );
    }

    public function reject(
        Payment $payment
    ): RedirectResponse {
        $result = DB::transaction(
            function () use ($payment) {
                $lockedPayment =
                    Payment::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $payment->id
                        );

                if (
                    $lockedPayment->status !==
                    'pending'
                ) {
                    return [
                        'success' => false,

                        'message' =>
                            'Only pending payments can be rejected.',
                    ];
                }

                $subscription = null;

                if (
                    $lockedPayment
                        ->subscription_id
                ) {
                    $subscription =
                        Subscription::query()
                            ->lockForUpdate()
                            ->find(
                                $lockedPayment
                                    ->subscription_id
                            );
                }

                /*
                |--------------------------------------------------------------------------
                | Release Pending Payment Guard
                |--------------------------------------------------------------------------
                */

                $lockedPayment->update([
                    'status' =>
                        'failed',

                    'pending_subscription_id' =>
                        null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Cancel Pending Subscription + Release Guard
                |--------------------------------------------------------------------------
                */

                if (
                    $subscription &&
                    $subscription->status ===
                    'pending'
                ) {
                    $subscription->update([
                        'status' =>
                            'cancelled',

                        'pending_user_id' =>
                            null,
                    ]);
                }

                return [
                    'success' => true,

                    'user_id' =>
                        $lockedPayment->user_id,
                ];
            },
            3
        );

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        $payment->load('user');

        if ($payment->user) {
            UserNotificationService::send(
                $payment->user,
                'Payment Rejected',
                'Your payment could not be verified. Please review your payment information and try again.',
                route(
                    'teacher.subscription.index'
                ),
                'payment'
            );
        }

        return back()->with(
            'success',
            'Payment rejected successfully.'
        );
    }
}