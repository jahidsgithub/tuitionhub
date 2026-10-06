@extends('layouts.app')

@section('title', 'Payments - Tuition Hub Admin')

@php
    $pageTitle = 'Payment Verification';
    $pageSubtitle = 'Admin Panel';
@endphp

@section('content')

    <div>
        <h2 class="text-2xl font-black text-slate-900">
            Payment Verification
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Review subscription payment submissions.
        </p>
    </div>

    <form
        method="GET"
        action="{{ route('admin.payments.index') }}"
        class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >
        <div class="grid gap-4 md:grid-cols-3">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Transaction ID, teacher..."
                class="rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >

            <select
                name="status"
                class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >
                <option value="">All Status</option>

                @foreach([
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'failed' => 'Failed',
                    'cancelled' => 'Cancelled',
                    'refunded' => 'Refunded',
                ] as $value => $label)

                    <option
                        value="{{ $value }}"
                        @selected(request('status') === $value)
                    >
                        {{ $label }}
                    </option>

                @endforeach
            </select>

            <div class="flex gap-2">

                <button
                    type="submit"
                    class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Filter
                </button>

                @if(
                    request()->filled('search') ||
                    request()->filled('status')
                )

                    <a
                        href="{{ route('admin.payments.index') }}"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600"
                    >
                        Clear
                    </a>

                @endif

            </div>

        </div>
    </form>

    <div class="mt-6 space-y-5">

        @forelse($payments as $payment)

            @php
                $statusClass = match($payment->status) {
                    'paid' => 'bg-emerald-50 text-emerald-700',
                    'pending' => 'bg-amber-50 text-amber-700',
                    'refunded' => 'bg-indigo-50 text-indigo-700',
                    default => 'bg-rose-50 text-rose-700',
                };

                $planName =
                    $payment->subscription?->plan_name_snapshot
                    ?: $payment->subscription?->plan?->name
                    ?: 'N/A';
            @endphp

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <div class="flex flex-col gap-6 lg:flex-row lg:justify-between">

                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-2">

                            <h3 class="text-xl font-bold text-slate-900">
                                {{ $payment->user?->name ?? 'Teacher' }}
                            </h3>

                            <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $statusClass }}">
                                {{ strtoupper($payment->status) }}
                            </span>

                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                            <div>
                                <p class="text-xs text-slate-400">Plan</p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $planName }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">Amount</p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    ৳{{ number_format((float) $payment->amount, 2) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Payment Method
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ strtoupper($payment->payment_method ?? 'N/A') }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Transaction ID
                                </p>

                                <p class="mt-1 break-all text-sm font-semibold text-slate-800">
                                    {{ $payment->transaction_id }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Sender Number
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{
                                        data_get(
                                            $payment->gateway_response,
                                            'sender_number',
                                            'N/A'
                                        )
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Submitted
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $payment->created_at->format('d M Y, h:i A') }}
                                </p>
                            </div>

                        </div>

                    </div>

                    @if($payment->status === 'pending')

                        <div class="space-y-3 lg:w-48">

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.payments.approve',
                                    $payment
                                ) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Approve this payment?')"
                                    class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700"
                                >
                                    Approve
                                </button>
                            </form>

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.payments.reject',
                                    $payment
                                ) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Reject this payment?')"
                                    class="w-full rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                                >
                                    Reject
                                </button>
                            </form>

                        </div>

                    @endif

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center text-sm text-slate-500">
                No payments found.
            </div>

        @endforelse

    </div>

    @if($payments->hasPages())
        <div class="mt-8">
            {{ $payments->links() }}
        </div>
    @endif

@endsection