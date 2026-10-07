@extends('layouts.app')

@section('title', 'Subscription - Tuition Hub')

@php
    $pageTitle = 'Subscription';
    $pageSubtitle = 'Teacher Subscription';
@endphp

@section('content')

<style>
    .th-plan-card {
        transition:
            transform .25s ease,
            box-shadow .25s ease,
            border-color .25s ease;
    }

    .th-plan-card:hover {
        transform: translateY(-6px);
    }

    .th-upgrade-button {
        display: flex !important;
        width: 100% !important;
        min-height: 50px !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        border: 0 !important;
        border-radius: 14px !important;
        background: linear-gradient(
            135deg,
            #4f46e5 0%,
            #7c3aed 100%
        ) !important;
        color: #ffffff !important;
        font-size: 14px !important;
        font-weight: 800 !important;
        line-height: 1.2 !important;
        cursor: pointer !important;
        box-shadow:
            0 10px 25px rgba(79, 70, 229, .22) !important;
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            opacity .2s ease !important;
    }

    .th-upgrade-button:hover {
        transform: translateY(-2px) !important;
        box-shadow:
            0 14px 30px rgba(79, 70, 229, .30) !important;
    }

    .th-upgrade-button:disabled {
        cursor: not-allowed !important;
        opacity: .55 !important;
        transform: none !important;
    }

    .th-renew-button {
        display: flex !important;
        width: 100% !important;
        min-height: 50px !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        border: 0 !important;
        border-radius: 14px !important;
        background: #059669 !important;
        color: #ffffff !important;
        font-size: 14px !important;
        font-weight: 800 !important;
        cursor: pointer !important;
        transition:
            transform .2s ease,
            background .2s ease !important;
    }

    .th-renew-button:hover {
        background: #047857 !important;
        transform: translateY(-2px) !important;
    }

    .th-normal-button {
        display: flex !important;
        width: 100% !important;
        min-height: 50px !important;
        align-items: center !important;
        justify-content: center !important;
        border: 0 !important;
        border-radius: 14px !important;
        background: #4f46e5 !important;
        color: #ffffff !important;
        font-size: 14px !important;
        font-weight: 800 !important;
        cursor: pointer !important;
    }
</style>


<div class="mx-auto max-w-7xl">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div>

        <div class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-indigo-700">
            Subscription Plans
        </div>

        <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">
            Grow Your Tuition Opportunities
        </h2>

        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
            Choose the plan that fits your teaching goals.
            Renew your current subscription or upgrade to a higher plan.
        </p>

    </div>


    {{-- =========================================================
         SUCCESS
    ========================================================== --}}
    @if(session('success'))

        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">

            <p class="font-bold text-emerald-800">
                Success
            </p>

            <p class="mt-1 text-sm text-emerald-700">
                {{ session('success') }}
            </p>

        </div>

    @endif


    {{-- =========================================================
         ERROR
    ========================================================== --}}
    @if(session('error'))

        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-4">

            <p class="font-bold text-rose-800">
                Something went wrong
            </p>

            <p class="mt-1 text-sm text-rose-700">
                {{ session('error') }}
            </p>

        </div>

    @endif


    {{-- =========================================================
         ACTIVE SUBSCRIPTION
    ========================================================== --}}
    @if($activeSubscription)

        <section class="relative mt-8 overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 p-6 text-white shadow-xl shadow-indigo-100 sm:p-8">

            <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-white/10"></div>

            <div class="relative">

                <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <span class="inline-flex rounded-full bg-white/15 px-3 py-1.5 text-xs font-bold uppercase tracking-wide">
                            Current Active Plan
                        </span>

                        <h2 class="mt-4 text-3xl font-black">
                            {{ $activeSubscription->plan?->name ?? 'Active Plan' }}
                        </h2>

                        <p class="mt-2 text-sm text-indigo-100">
                            Your subscription is active and ready for tuition applications.
                        </p>

                    </div>


                    <div class="grid gap-3 sm:grid-cols-3">

                        <div class="rounded-2xl bg-white/10 p-4">

                            <p class="text-xs uppercase tracking-wide text-indigo-100">
                                Applications
                            </p>

                            <p class="mt-2 font-black">

                                @if(
                                    $activeSubscription
                                        ->plan
                                        ?->application_limit === null
                                )

                                    Unlimited

                                @else

                                    {{ $activeSubscription->applications_used }}
                                    /
                                    {{ $activeSubscription->plan->application_limit }}

                                @endif

                            </p>

                        </div>


                        <div class="rounded-2xl bg-white/10 p-4">

                            <p class="text-xs uppercase tracking-wide text-indigo-100">
                                Started
                            </p>

                            <p class="mt-2 font-black">
                                {{
                                    $activeSubscription
                                        ->starts_at
                                        ?->format('d M Y')
                                    ?? 'N/A'
                                }}
                            </p>

                        </div>


                        <div class="rounded-2xl bg-white/10 p-4">

                            <p class="text-xs uppercase tracking-wide text-indigo-100">
                                Expires
                            </p>

                            <p class="mt-2 font-black">
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

            </div>

        </section>

    @else

        <section class="mt-8 rounded-3xl border border-amber-200 bg-amber-50 p-6">

            <h3 class="font-black text-amber-900">
                No active subscription
            </h3>

            <p class="mt-1 text-sm text-amber-700">
                Choose a plan below to start applying for tuition opportunities.
            </p>

        </section>

    @endif


    {{-- =========================================================
         PENDING SUBSCRIPTION
    ========================================================== --}}
    @if($pendingSubscription)

        <section class="mt-6 rounded-3xl border border-orange-200 bg-white p-5 shadow-sm">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="text-xs font-black uppercase tracking-wider text-orange-500">
                        Pending Subscription
                    </p>

                    <h3 class="mt-1 text-xl font-black text-slate-900">
                        {{ $pendingSubscription->plan?->name }}
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">

                        Amount:

                        <strong class="text-orange-600">

                            ৳{{
                                number_format(
                                    (float) $pendingSubscription->amount,
                                    2
                                )
                            }}

                        </strong>

                    </p>

                </div>


                @if((float) $pendingSubscription->amount > 0)

                    <a
                        href="{{ route(
                            'teacher.payment.create',
                            $pendingSubscription
                        ) }}"
                        class="rounded-xl bg-orange-600 px-5 py-3 text-center text-sm font-bold text-white"
                    >
                        Payment Details
                    </a>

                @endif

            </div>

        </section>

    @endif


    {{-- =========================================================
         AVAILABLE PLANS
    ========================================================== --}}
    <section class="mt-12">

        <p class="text-xs font-black uppercase tracking-[0.2em] text-indigo-600">
            Pricing
        </p>

        <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">
            Available Plans
        </h2>

        <p class="mt-2 text-sm text-slate-500">
            Choose the subscription level that best matches your tuition application needs.
        </p>


        <div class="mt-7 grid gap-6 md:grid-cols-2 xl:grid-cols-3">

            @foreach($plans as $plan)

                @php
                    /*
                    |--------------------------------------------------------------------------
                    | Current Plan
                    |--------------------------------------------------------------------------
                    */
                    $isCurrentPlan =
                        $activeSubscription &&
                        (int) $activeSubscription->subscription_plan_id ===
                        (int) $plan->id;


                    /*
                    |--------------------------------------------------------------------------
                    | Other states
                    |--------------------------------------------------------------------------
                    */
                    $isFree =
                        (float) $plan->price === 0.0;

                    $hasPending =
                        $pendingSubscription !== null;

                    $currentPlan =
                        $activeSubscription?->plan;


                    /*
                    |--------------------------------------------------------------------------
                    | Upgrade detection
                    |--------------------------------------------------------------------------
                    |
                    | 1. First use sort_order.
                    | 2. If sort_order is equal, use price.
                    |
                    */

                    $isUpgrade = false;

                    $isDowngrade = false;


                    if (
                        $activeSubscription &&
                        $currentPlan &&
                        ! $isCurrentPlan
                    ) {

                        $currentSort =
                            (int) ($currentPlan->sort_order ?? 0);

                        $targetSort =
                            (int) ($plan->sort_order ?? 0);


                        if ($targetSort > $currentSort) {

                            $isUpgrade = true;

                        } elseif ($targetSort < $currentSort) {

                            $isDowngrade = true;

                        } else {

                            if (
                                (float) $plan->price >
                                (float) $currentPlan->price
                            ) {

                                $isUpgrade = true;

                            } elseif (
                                (float) $plan->price <
                                (float) $currentPlan->price
                            ) {

                                $isDowngrade = true;
                            }
                        }
                    }
                @endphp


                <article
                    class="
                        th-plan-card
                        relative flex h-full flex-col overflow-hidden rounded-3xl border bg-white p-7

                        @if($plan->is_featured)
                            border-indigo-400 shadow-xl shadow-indigo-100

                        @elseif($isCurrentPlan)
                            border-emerald-300 shadow-lg shadow-emerald-100

                        @elseif($isUpgrade)
                            border-violet-300 shadow-lg shadow-violet-100

                        @else
                            border-slate-200 shadow-sm
                        @endif
                    "
                >

                    {{-- =====================================================
                         BADGES
                    ====================================================== --}}
                    <div class="flex min-h-[30px] flex-wrap gap-2">

                        @if($isCurrentPlan)

                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-black uppercase text-emerald-700">
                                ● Current Plan
                            </span>

                        @endif


                        @if($plan->is_featured)

                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-[11px] font-black uppercase text-indigo-700">
                                ★ Most Popular
                            </span>

                        @endif


                        @if($isUpgrade)

                            <span class="rounded-full bg-violet-50 px-3 py-1 text-[11px] font-black uppercase text-violet-700">
                                ↑ Upgrade
                            </span>

                        @endif

                    </div>


                    {{-- =====================================================
                         ICON
                    ====================================================== --}}
                    <div
                        class="
                            mt-5 flex h-12 w-12 items-center justify-center rounded-2xl

                            @if($plan->is_featured || $isUpgrade)
                                bg-gradient-to-br from-violet-300 via-purple-400 to-violet-600 text-white shadow-lg shadow-violet-200

                            @elseif($isCurrentPlan)
                                bg-emerald-100 text-emerald-700

                            @else
                                bg-slate-100 text-slate-700
                            @endif
                        "
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-7 w-7"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 3l7 4v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V7l7-4z"
                            />
                        </svg>

                    </div>


                    {{-- =====================================================
                         PLAN INFO
                    ====================================================== --}}
                    <h3 class="mt-5 text-2xl font-black text-slate-900">
                        {{ $plan->name }}
                    </h3>


                    <p class="mt-2 min-h-[45px] text-sm leading-6 text-slate-500">

                        {{
                            $plan->description
                            ?? 'Tuition Hub subscription plan.'
                        }}

                    </p>


                    {{-- =====================================================
                         PRICE
                    ====================================================== --}}
                    <div class="mt-6">

                        @if($isFree)

                            <span class="text-4xl font-black text-slate-900">
                                Free
                            </span>

                        @else

                            <div class="flex items-end gap-2">

                                <span class="pb-1 text-lg font-bold text-slate-400">
                                    ৳
                                </span>

                                <span
                                    class="
                                        text-4xl font-black

                                        @if($isUpgrade || $plan->is_featured)
                                            text-indigo-700
                                        @else
                                            text-slate-900
                                        @endif
                                    "
                                >
                                    {{
                                        number_format(
                                            (float) $plan->price
                                        )
                                    }}
                                </span>

                            </div>

                        @endif

                    </div>


                    <div class="my-6 border-t border-slate-100"></div>


                    {{-- =====================================================
                         FEATURES
                    ====================================================== --}}
                    <div class="space-y-4">

                        {{-- Duration --}}
                        <div class="flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <rect
                                        x="3"
                                        y="5"
                                        width="18"
                                        height="16"
                                        rx="2"
                                    />

                                    <path d="M16 3v4M8 3v4M3 10h18"/>
                                </svg>

                            </div>


                            <div>

                                <p class="text-xs font-semibold uppercase text-slate-400">
                                    Duration
                                </p>

                                <p class="font-black text-slate-800">
                                    {{ $plan->duration_days }} days
                                </p>

                            </div>

                        </div>


                        {{-- Application Limit --}}
                        <div class="flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path d="M8 6h13M8 12h13M8 18h13"/>
                                    <path d="M3 6h.01M3 12h.01M3 18h.01"/>
                                </svg>

                            </div>


                            <div>

                                <p class="text-xs font-semibold uppercase text-slate-400">
                                    Application Limit
                                </p>

                                <p class="font-black text-slate-800">

                                    @if($plan->application_limit === null)

                                        Unlimited applications

                                    @else

                                        {{ $plan->application_limit }}
                                        applications

                                    @endif

                                </p>

                            </div>

                        </div>


                        {{-- Access --}}
                        <div class="flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.4"
                                    class="h-5 w-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5 12.5l4 4L19 7"
                                    />
                                </svg>

                            </div>


                            <div>

                                <p class="text-xs font-semibold uppercase text-slate-400">
                                    Access
                                </p>

                                <p class="font-black text-slate-800">
                                    Tuition marketplace access
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- =====================================================
                         ACTION
                    ====================================================== --}}
                    <div class="mt-auto pt-7">

                        {{-- =================================================
                             CURRENT PLAN
                        ================================================== --}}
                        @if($isCurrentPlan)

                            @if((float) $plan->price > 0)

                                @if($hasPending)

                                    <button
                                        type="button"
                                        disabled
                                        class="th-renew-button"
                                        style="opacity:.55;"
                                    >
                                        Renew {{ $plan->name }}
                                    </button>

                                    <p class="mt-2 text-center text-xs font-semibold text-orange-600">
                                        Complete pending payment first.
                                    </p>

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
                                            class="th-renew-button"
                                        >

                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                style="
                                                    width:18px;
                                                    height:18px;
                                                "
                                            >
                                                <path d="M4 12a8 8 0 0 1 14-5"/>
                                                <path d="M18 3v4h-4"/>
                                            </svg>

                                            Renew {{ $plan->name }}

                                        </button>

                                    </form>

                                @endif


                            @else

                                <button
                                    type="button"
                                    disabled
                                    class="w-full cursor-not-allowed rounded-xl bg-emerald-50 px-4 py-3.5 text-sm font-black text-emerald-700"
                                >
                                    Current Free Plan
                                </button>

                            @endif


                        {{-- =================================================
                             UPGRADE
                             THIS IS THE PRO BUTTON
                        ================================================== --}}
                        @elseif($isUpgrade)

                            @if($hasPending)

                                <button
                                    type="button"
                                    disabled
                                    class="th-upgrade-button"
                                >

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.4"
                                        style="
                                            width:18px;
                                            height:18px;
                                        "
                                    >
                                        <path d="M12 19V5"/>
                                        <path d="M6 11l6-6 6 6"/>
                                    </svg>

                                    Upgrade to {{ $plan->name }}

                                </button>


                                <p class="mt-2 text-center text-xs font-semibold text-orange-600">
                                    Complete pending payment first.
                                </p>


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
                                        class="th-upgrade-button"
                                    >

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2.4"
                                            style="
                                                width:18px;
                                                height:18px;
                                            "
                                        >
                                            <path d="M12 19V5"/>
                                            <path d="M6 11l6-6 6 6"/>
                                        </svg>


                                        <span style="color:#ffffff !important;">
                                            Upgrade to {{ $plan->name }}
                                        </span>

                                    </button>

                                </form>

                            @endif


                        {{-- =================================================
                             DOWNGRADE
                        ================================================== --}}
                        @elseif($isDowngrade)

                            <button
                                type="button"
                                disabled
                                class="w-full cursor-not-allowed rounded-xl bg-slate-100 px-4 py-3.5 text-sm font-bold text-slate-400"
                            >
                                Available After Expiry
                            </button>


                        {{-- =================================================
                             CHOOSE PLAN
                        ================================================== --}}
                        @else

                            @if($hasPending)

                                <button
                                    type="button"
                                    disabled
                                    class="w-full cursor-not-allowed rounded-xl bg-slate-100 px-4 py-3.5 text-sm font-bold text-slate-400"
                                >

                                    @if($isFree)

                                        Activate Free Plan

                                    @else

                                        Choose {{ $plan->name }}

                                    @endif

                                </button>


                                <p class="mt-2 text-center text-xs font-semibold text-orange-600">
                                    Complete pending payment first.
                                </p>


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
                                        class="th-normal-button"
                                    >

                                        @if($isFree)

                                            Activate Free Plan

                                        @else

                                            Choose {{ $plan->name }}

                                        @endif

                                    </button>

                                </form>

                            @endif

                        @endif

                    </div>

                </article>

            @endforeach

        </div>

    </section>


    {{-- =========================================================
         SUBSCRIPTION HISTORY
    ========================================================== --}}
    <section class="mt-14">

        <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">
            Activity
        </p>


        <h2 class="mt-2 text-2xl font-black text-slate-900">
            Subscription History
        </h2>


        <div class="mt-5 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[760px] text-sm">

                    <thead class="bg-slate-50">

                        <tr class="text-left text-xs uppercase tracking-wide text-slate-400">

                            <th class="px-6 py-4">
                                Plan
                            </th>

                            <th class="px-6 py-4">
                                Amount
                            </th>

                            <th class="px-6 py-4">
                                Status
                            </th>

                            <th class="px-6 py-4">
                                Started
                            </th>

                            <th class="px-6 py-4">
                                Expires
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @forelse($subscriptionHistory as $subscription)

                            <tr class="hover:bg-slate-50">

                                <td class="px-6 py-4 font-black text-slate-800">

                                    {{
                                        $subscription
                                            ->plan
                                            ?->name
                                        ?? 'N/A'
                                    }}

                                </td>


                                <td class="px-6 py-4 font-semibold text-slate-700">

                                    ৳{{
                                        number_format(
                                            (float) $subscription->amount,
                                            2
                                        )
                                    }}

                                </td>


                                <td class="px-6 py-4">

                                    <span
                                        class="
                                            rounded-full px-3 py-1 text-xs font-black

                                            @if($subscription->status === 'active')
                                                bg-emerald-50 text-emerald-700

                                            @elseif($subscription->status === 'pending')
                                                bg-amber-50 text-amber-700

                                            @elseif($subscription->status === 'expired')
                                                bg-slate-100 text-slate-600

                                            @elseif($subscription->status === 'cancelled')
                                                bg-rose-50 text-rose-700

                                            @else
                                                bg-slate-100 text-slate-600
                                            @endif
                                        "
                                    >

                                        {{ strtoupper($subscription->status) }}

                                    </span>

                                </td>


                                <td class="px-6 py-4 text-slate-600">

                                    {{
                                        $subscription
                                            ->starts_at
                                            ?->format('d M Y')
                                        ?? '—'
                                    }}

                                </td>


                                <td class="px-6 py-4 text-slate-600">

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
                                    class="px-6 py-12 text-center text-slate-500"
                                >
                                    No subscription history yet.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </section>

</div>

@endsection