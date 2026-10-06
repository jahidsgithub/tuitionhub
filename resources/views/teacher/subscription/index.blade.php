@extends('layouts.app')

@section('title', 'Subscription - Tuition Hub')

@php
    $pageTitle = 'Subscription';
    $pageSubtitle = 'Teacher Account';

    $activePlanName =
        $activeSubscription?->plan_name_snapshot
        ?: $activeSubscription?->plan?->name
        ?: 'Active Plan';

    $activeApplicationLimit = $activeSubscription
        ? (
            $activeSubscription->plan_snapshot_captured_at
                ? $activeSubscription->application_limit_snapshot
                : $activeSubscription->plan?->application_limit
        )
        : null;
@endphp

@section('content')

    <div>

        <h2 class="text-2xl font-black text-slate-900">
            Subscription
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Choose, renew or upgrade your tuition application plan.
        </p>

    </div>

    @if($activeSubscription)

        <section class="mt-6 rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 p-6 text-white shadow-lg sm:p-8">

            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <p class="text-sm font-medium text-indigo-100">
                        Current Active Plan
                    </p>

                    <h3 class="mt-2 text-3xl font-black">
                        {{ $activePlanName }}
                    </h3>

                </div>

                <div class="grid gap-5 sm:grid-cols-3">

                    <div>

                        <p class="text-sm text-indigo-200">
                            Applications
                        </p>

                        <p class="mt-1 font-bold">

                            @if($activeApplicationLimit === null)

                                Unlimited

                            @else

                                {{ $activeSubscription->applications_used }}
                                /
                                {{ $activeApplicationLimit }}

                            @endif

                        </p>

                    </div>

                    <div>

                        <p class="text-sm text-indigo-200">
                            Started
                        </p>

                        <p class="mt-1 font-bold">
                            {{
                                $activeSubscription
                                    ->starts_at
                                    ?->format('d M Y')
                                ?? 'N/A'
                            }}
                        </p>

                    </div>

                    <div>

                        <p class="text-sm text-indigo-200">
                            Expires
                        </p>

                        <p class="mt-1 font-bold">
                            {{
                                $activeSubscription
                                    ->expires_at
                                    ?->format('d M Y')
                                ?? 'No expiry'
                            }}
                        </p>

                    </div>

                </div>

            </div>

            <p class="mt-6 text-sm leading-6 text-indigo-100">
                You may renew the same plan now. Remaining subscription time
                will be preserved. Eligible higher-priced plans can also be
                selected as upgrades.
            </p>

        </section>

    @else

        <section class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">

            <h3 class="font-bold text-amber-900">
                No active subscription
            </h3>

            <p class="mt-1 text-sm text-amber-700">
                Select a plan below to start applying for tuition opportunities.
            </p>

        </section>

    @endif

    @if($pendingSubscription)

        <section class="mt-5 rounded-2xl border border-orange-200 bg-orange-50 p-5">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="text-sm text-orange-700">
                        Pending Subscription
                    </p>

                    <h3 class="mt-1 font-bold text-orange-900">
                        {{
                            $pendingSubscription->plan_name_snapshot
                            ?: $pendingSubscription->plan?->name
                            ?: 'Subscription'
                        }}
                    </h3>

                    <p class="mt-1 text-sm text-orange-700">
                        Amount:
                        <strong>
                            ৳{{ number_format(
                                (float) $pendingSubscription->amount,
                                2
                            ) }}
                        </strong>
                    </p>

                </div>

                @if((float) $pendingSubscription->amount > 0)

                    <a
                        href="{{ route(
                            'teacher.payment.create',
                            $pendingSubscription
                        ) }}"
                        class="inline-flex justify-center rounded-xl bg-orange-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-700"
                    >
                        Payment Details
                    </a>

                @endif

            </div>

        </section>

    @endif

    <section class="mt-8">

        <div>

            <h3 class="text-xl font-black text-slate-900">
                Available Plans
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Select the plan that fits your tuition application needs.
            </p>

        </div>

        <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">

            @foreach($plans as $plan)

                @php
                    $isCurrentPlan =
                        $activeSubscription &&
                        $activeSubscription->subscription_plan_id === $plan->id;

                    $activePlanPrice =
                        $activeSubscription?->amount
                        ?? $activeSubscription?->plan?->price
                        ?? 0;

                    $isUpgrade =
                        $activeSubscription &&
                        ! $isCurrentPlan &&
                        (float) $plan->price >
                        (float) $activePlanPrice;

                    $isDowngrade =
                        $activeSubscription &&
                        ! $isCurrentPlan &&
                        (float) $plan->price <=
                        (float) $activePlanPrice;

                    $hasPending =
                        $pendingSubscription !== null;
                @endphp

                <article class="relative rounded-2xl border bg-white p-6 shadow-sm {{ $plan->is_featured ? 'border-indigo-400' : 'border-slate-200' }}">

                    @if($plan->is_featured)

                        <span class="absolute right-4 top-4 rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-bold text-indigo-700">
                            POPULAR
                        </span>

                    @endif

                    @if($isCurrentPlan)

                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold text-emerald-700">
                            CURRENT PLAN
                        </span>

                    @endif

                    <h4 class="mt-4 text-2xl font-black text-slate-900">
                        {{ $plan->name }}
                    </h4>

                    <p class="mt-2 min-h-[40px] text-sm leading-6 text-slate-500">
                        {{ $plan->description ?? 'Tuition Hub subscription plan.' }}
                    </p>

                    <div class="mt-6">

                        @if((float) $plan->price === 0.0)

                            <span class="text-4xl font-black text-slate-900">
                                Free
                            </span>

                        @else

                            <span class="text-4xl font-black text-slate-900">
                                ৳{{ number_format(
                                    (float) $plan->price
                                ) }}
                            </span>

                        @endif

                    </div>

                    <div class="mt-6 space-y-3 text-sm">

                        <div class="flex items-center justify-between">

                            <span class="text-slate-500">
                                Duration
                            </span>

                            <strong class="text-slate-800">
                                {{ $plan->duration_days }} days
                            </strong>

                        </div>

                        <div class="flex items-center justify-between">

                            <span class="text-slate-500">
                                Application Limit
                            </span>

                            <strong class="text-slate-800">

                                @if($plan->application_limit === null)

                                    Unlimited

                                @else

                                    {{ $plan->application_limit }}

                                @endif

                            </strong>

                        </div>

                    </div>

                    <div class="mt-7">

                        @if($hasPending)

                            <button
                                type="button"
                                disabled
                                class="w-full cursor-not-allowed rounded-xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-400"
                            >
                                Pending Payment Exists
                            </button>

                        @elseif($isCurrentPlan)

                            @if((float) $plan->price > 0)

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'teacher.subscription.subscribe',
                                        $plan
                                    ) }}"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        onclick="return confirm('Renew this subscription? Your remaining days will be preserved after payment approval.')"
                                        class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700"
                                    >
                                        Renew Plan
                                    </button>

                                </form>

                            @else

                                <button
                                    type="button"
                                    disabled
                                    class="w-full cursor-not-allowed rounded-xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-400"
                                >
                                    Current Free Plan
                                </button>

                            @endif

                        @elseif($isUpgrade)

                            <form
                                method="POST"
                                action="{{ route(
                                    'teacher.subscription.subscribe',
                                    $plan
                                ) }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    onclick="return confirm('Upgrade to this plan? The upgraded plan will become active after payment approval.')"
                                    class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                                >
                                    Upgrade to {{ $plan->name }}
                                </button>

                            </form>

                        @elseif($isDowngrade)

                            <button
                                type="button"
                                disabled
                                class="w-full cursor-not-allowed rounded-xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-400"
                            >
                                Available After Expiry
                            </button>

                        @else

                            <form
                                method="POST"
                                action="{{ route(
                                    'teacher.subscription.subscribe',
                                    $plan
                                ) }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                                >
                                    @if((float) $plan->price === 0.0)

                                        Activate Free Plan

                                    @else

                                        Choose Plan

                                    @endif
                                </button>

                            </form>

                        @endif

                    </div>

                </article>

            @endforeach

        </div>

    </section>

    <section class="mt-10">

        <h3 class="text-xl font-black text-slate-900">
            Subscription History
        </h3>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">

            <table class="min-w-full text-sm">

                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">

                    <tr>

                        <th class="px-5 py-4">
                            Plan
                        </th>

                        <th class="px-5 py-4">
                            Amount
                        </th>

                        <th class="px-5 py-4">
                            Status
                        </th>

                        <th class="px-5 py-4">
                            Started
                        </th>

                        <th class="px-5 py-4">
                            Expires
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($subscriptionHistory as $subscription)

                        @php
                            $historyStatusClass = match($subscription->status) {
                                'active' => 'bg-emerald-50 text-emerald-700',
                                'pending' => 'bg-amber-50 text-amber-700',
                                'expired' => 'bg-slate-100 text-slate-600',
                                'cancelled' => 'bg-rose-50 text-rose-700',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp

                        <tr>

                            <td class="px-5 py-4 font-semibold text-slate-900">
                                {{
                                    $subscription->plan_name_snapshot
                                    ?: $subscription->plan?->name
                                    ?: 'N/A'
                                }}
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                ৳{{ number_format(
                                    (float) $subscription->amount,
                                    2
                                ) }}
                            </td>

                            <td class="px-5 py-4">

                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $historyStatusClass }}">
                                    {{ strtoupper($subscription->status) }}
                                </span>

                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                {{
                                    $subscription
                                        ->starts_at
                                        ?->format('d M Y')
                                    ?? '—'
                                }}
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                {{
                                    $subscription
                                        ->expires_at
                                        ?->format('d M Y')
                                    ?? '—'
                                }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="px-5 py-12 text-center text-slate-500"
                            >
                                No subscription history yet.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>

@endsection