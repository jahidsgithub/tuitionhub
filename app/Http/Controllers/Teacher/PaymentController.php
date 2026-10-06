<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Models\Subscription;
use App\Services\AdminNotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function create(
        Subscription $subscription
    ): View|RedirectResponse {
        $this->authorizeSubscription(
            $subscription
        );

        $subscription->load([
            'plan',
            'payments',
        ]);

        if (
            $subscription->status !==
            'pending'
        ) {
            return redirect()
                ->route(
                    'teacher.subscription.index'
                )
                ->with(
                    'error',
                    'This subscription is no longer awaiting payment.'
                );
        }

        $paidPayment =
            Payment::query()
                ->where(
                    'subscription_id',
                    $subscription->id
                )
                ->where(
                    'status',
                    'paid'
                )
                ->latest()
                ->first();

        if ($paidPayment) {
            return redirect()
                ->route(
                    'teacher.subscription.index'
                )
                ->with(
                    'error',
                    'Payment for this subscription has already been approved.'
                );
        }

        $existingPayment =
            Payment::query()
                ->where(
                    'subscription_id',
                    $subscription->id
                )
                ->where(
                    'status',
                    'pending'
                )
                ->latest()
                ->first();

        $paymentSetting =
            PaymentSetting::current();

        return view(
            'teacher.payment.create',
            compact(
                'subscription',
                'paymentSetting',
                'existingPayment'
            )
        );
    }

    public function store(
        Request $request,
        Subscription $subscription
    ): RedirectResponse {
        $this->authorizeSubscription(
            $subscription
        );

        $validated =
            $request->validate([
                'payment_method' => [
                    'required',
                    'in:bkash,nagad',
                ],

                'sender_number' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'transaction_id' => [
                    'required',
                    'string',
                    'max:100',
                ],
            ]);

        $paymentMethod =
            strtolower(
                trim(
                    $validated[
                        'payment_method'
                    ]
                )
            );

        $senderNumber =
            trim(
                $validated[
                    'sender_number'
                ]
            );

        $transactionId =
            trim(
                $validated[
                    'transaction_id'
                ]
            );

        try {
            $result = DB::transaction(
                function () use (
                    $subscription,
                    $paymentMethod,
                    $senderNumber,
                    $transactionId
                ) {
                    $lockedSubscription =
                        Subscription::query()
                            ->with([
                                'plan',
                                'user',
                            ])
                            ->lockForUpdate()
                            ->findOrFail(
                                $subscription->id
                            );

                    if (
                        $lockedSubscription->user_id !==
                        auth()->id()
                    ) {
                        abort(403);
                    }

                    if (
                        $lockedSubscription->status !==
                        'pending'
                    ) {
                        return [
                            'success' => false,
                            'message' =>
                                'This subscription is no longer awaiting payment.',
                        ];
                    }

                    if (
                        (float)
                        $lockedSubscription->amount
                        <= 0
                    ) {
                        return [
                            'success' => false,
                            'message' =>
                                'This subscription does not require payment.',
                        ];
                    }

                    $paymentSetting =
                        PaymentSetting::query()
                            ->lockForUpdate()
                            ->first();

                    if (! $paymentSetting) {
                        return [
                            'success' => false,
                            'message' =>
                                'Payment settings are not configured yet.',
                        ];
                    }

                    if (
                        $paymentMethod === 'bkash' &&
                        (
                            ! $paymentSetting->bkash_enabled ||
                            ! $paymentSetting->bkash_number
                        )
                    ) {
                        return [
                            'success' => false,
                            'message' =>
                                'bKash payment is currently unavailable.',
                        ];
                    }

                    if (
                        $paymentMethod === 'nagad' &&
                        (
                            ! $paymentSetting->nagad_enabled ||
                            ! $paymentSetting->nagad_number
                        )
                    ) {
                        return [
                            'success' => false,
                            'message' =>
                                'Nagad payment is currently unavailable.',
                        ];
                    }

                    $paidPayment =
                        Payment::query()
                            ->where(
                                'subscription_id',
                                $lockedSubscription->id
                            )
                            ->where(
                                'status',
                                'paid'
                            )
                            ->lockForUpdate()
                            ->first();

                    if ($paidPayment) {
                        return [
                            'success' => false,
                            'message' =>
                                'A payment for this subscription has already been approved.',
                        ];
                    }

                    $pendingPayment =
                        Payment::query()
                            ->where(
                                'subscription_id',
                                $lockedSubscription->id
                            )
                            ->where(
                                'status',
                                'pending'
                            )
                            ->lockForUpdate()
                            ->first();

                    if ($pendingPayment) {
                        return [
                            'success' => false,
                            'message' =>
                                'A payment submission for this subscription is already pending admin review.',
                        ];
                    }

                    $duplicateTransaction =
                        Payment::query()
                            ->where(
                                'transaction_id',
                                $transactionId
                            )
                            ->first();

                    if ($duplicateTransaction) {
                        return [
                            'success' => false,
                            'message' =>
                                'This transaction ID has already been submitted.',
                        ];
                    }

                    $payment =
                        Payment::create([
                            'user_id' =>
                                auth()->id(),

                            'subscription_id' =>
                                $lockedSubscription->id,

                            'transaction_id' =>
                                $transactionId,

                            'payment_method' =>
                                $paymentMethod,

                            'amount' =>
                                $lockedSubscription->amount,

                            'currency' =>
                                'BDT',

                            'status' =>
                                'pending',

                            'pending_subscription_id' =>
                                $lockedSubscription->id,

                            'gateway_response' => [
                                'sender_number' =>
                                    $senderNumber,

                                'submission_type' =>
                                    'manual',

                                'submitted_at' =>
                                    now()->toIso8601String(),
                            ],

                            'paid_at' =>
                                null,
                        ]);

                    return [
                        'success' => true,
                        'payment_id' =>
                            $payment->id,
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
                return back()
                    ->withInput(
                        $request->except(
                            'transaction_id'
                        )
                    )
                    ->with(
                        'error',
                        'This payment or transaction has already been submitted.'
                    );
            }

            throw $e;
        }

        if (! $result['success']) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    $result['message']
                );
        }

        $payment =
            Payment::query()
                ->with([
                    'user',
                    'subscription.plan',
                ])
                ->find(
                    $result['payment_id']
                );

        if ($payment) {
            AdminNotificationService::send(
                'New Payment Awaiting Review',
                $payment->user->name
                    .' submitted '
                    .strtoupper(
                        $payment->payment_method
                    )
                    .' payment ৳'
                    .number_format(
                        (float) $payment->amount,
                        2
                    )
                    .'.',
                route(
                    'admin.payments.index',
                    [
                        'status' => 'pending',
                        'search' =>
                            $payment->transaction_id,
                    ]
                ),
                'payment'
            );
        }

        return redirect()
            ->route(
                'teacher.subscription.index'
            )
            ->with(
                'success',
                'Payment submitted successfully. Please wait for admin verification.'
            );
    }

    private function authorizeSubscription(
        Subscription $subscription
    ): void {
        abort_unless(
            $subscription->user_id ===
            auth()->id(),
            403
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