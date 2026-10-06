@extends('layouts.app')

@section('title', 'Subscription Payment - Tuition Hub')

@php
    $pageTitle = 'Subscription Payment';
    $pageSubtitle = 'Teacher Account';

    $subscriptionName =
        $subscription->plan_name_snapshot
        ?: $subscription->plan?->name
        ?: 'Subscription Plan';
@endphp

@section('content')

    <div class="mx-auto max-w-3xl">

        <a
            href="{{ route('teacher.subscription.index') }}"
            class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
        >
            ← Back to Subscription
        </a>

        <div class="mt-5">

            <h2 class="text-2xl font-black text-slate-900">
                Subscription Payment
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Complete the payment and submit your transaction information.
            </p>

        </div>

        <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">

            <p class="text-sm text-slate-500">
                Subscription Plan
            </p>

            <h3 class="mt-1 text-2xl font-black text-slate-900">
                {{ $subscriptionName }}
            </h3>

            <div class="mt-5">

                <p class="text-sm text-slate-500">
                    Amount to Pay
                </p>

                <p class="mt-1 text-3xl font-black text-indigo-600">
                    ৳{{ number_format(
                        (float) $subscription->amount,
                        2
                    ) }}
                </p>

            </div>

        </section>

        @if(
            ! $paymentSetting->bkash_enabled &&
            ! $paymentSetting->nagad_enabled
        )

            <section class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5">

                <h3 class="font-bold text-amber-900">
                    Payment is temporarily unavailable
                </h3>

                <p class="mt-2 text-sm leading-6 text-amber-700">
                    No manual payment method is currently enabled.
                    Please contact Tuition Hub support.
                </p>

            </section>

        @else

            <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h3 class="text-lg font-bold text-slate-900">
                    Payment Accounts
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Send the exact subscription amount to one of the available accounts.
                </p>

                <div class="mt-5 space-y-4">

                    @if($paymentSetting->bkash_enabled)

                        <div class="rounded-xl border border-pink-100 bg-pink-50/50 p-4">

                            <div class="flex items-start justify-between gap-4">

                                <div>

                                    <p class="font-bold text-pink-600">
                                        bKash
                                    </p>

                                    <p class="mt-2 text-xl font-black text-slate-900">
                                        {{ $paymentSetting->bkash_number }}
                                    </p>

                                </div>

                                @if($paymentSetting->bkash_account_type)

                                    <span class="rounded-full bg-white px-3 py-1 text-[10px] font-bold text-pink-700">
                                        {{ strtoupper(
                                            $paymentSetting->bkash_account_type
                                        ) }}
                                    </span>

                                @endif

                            </div>

                        </div>

                    @endif

                    @if($paymentSetting->nagad_enabled)

                        <div class="rounded-xl border border-orange-100 bg-orange-50/50 p-4">

                            <div class="flex items-start justify-between gap-4">

                                <div>

                                    <p class="font-bold text-orange-600">
                                        Nagad
                                    </p>

                                    <p class="mt-2 text-xl font-black text-slate-900">
                                        {{ $paymentSetting->nagad_number }}
                                    </p>

                                </div>

                                @if($paymentSetting->nagad_account_type)

                                    <span class="rounded-full bg-white px-3 py-1 text-[10px] font-bold text-orange-700">
                                        {{ strtoupper(
                                            $paymentSetting->nagad_account_type
                                        ) }}
                                    </span>

                                @endif

                            </div>

                        </div>

                    @endif

                </div>

                @if($paymentSetting->payment_instruction)

                    <div class="mt-5 rounded-xl bg-slate-50 p-4">

                        <p class="font-semibold text-slate-800">
                            Payment Instructions
                        </p>

                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">
                            {{ $paymentSetting->payment_instruction }}
                        </p>

                    </div>

                @endif

            </section>

            @if(
                $existingPayment &&
                $existingPayment->status === 'pending'
            )

                <section class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5">

                    <h3 class="font-bold text-amber-900">
                        Payment Verification Pending
                    </h3>

                    <p class="mt-2 text-sm text-amber-700">
                        Transaction ID:
                        <strong>
                            {{ $existingPayment->transaction_id }}
                        </strong>
                    </p>

                    <p class="mt-1 text-sm text-amber-700">
                        Your submitted payment is waiting for admin verification.
                    </p>

                </section>

            @else

                <form
                    method="POST"
                    action="{{ route(
                        'teacher.payment.store',
                        $subscription
                    ) }}"
                    class="mt-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    @csrf

                    <h3 class="text-lg font-bold text-slate-900">
                        Submit Payment Information
                    </h3>

                    <div class="mt-5">

                        <label
                            for="payment_method"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Payment Method
                        </label>

                        <select
                            id="payment_method"
                            name="payment_method"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >
                            <option value="">
                                Select payment method
                            </option>

                            @if($paymentSetting->bkash_enabled)

                                <option
                                    value="bkash"
                                    @selected(
                                        old('payment_method') === 'bkash'
                                    )
                                >
                                    bKash
                                </option>

                            @endif

                            @if($paymentSetting->nagad_enabled)

                                <option
                                    value="nagad"
                                    @selected(
                                        old('payment_method') === 'nagad'
                                    )
                                >
                                    Nagad
                                </option>

                            @endif
                        </select>

                    </div>

                    <div class="mt-5">

                        <label
                            for="sender_number"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Your Sender Number
                        </label>

                        <input
                            id="sender_number"
                            type="text"
                            name="sender_number"
                            value="{{ old('sender_number') }}"
                            required
                            placeholder="01XXXXXXXXX"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >

                    </div>

                    <div class="mt-5">

                        <label
                            for="transaction_id"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Transaction ID
                        </label>

                        <input
                            id="transaction_id"
                            type="text"
                            name="transaction_id"
                            value="{{ old('transaction_id') }}"
                            required
                            placeholder="Example: 9ABC123XYZ"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >

                    </div>

                    <button
                        type="submit"
                        class="mt-6 w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                    >
                        Submit Payment for Verification
                    </button>

                </form>

            @endif

        @endif

    </div>

@endsection