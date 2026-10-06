@extends('layouts.app')

@section('title', 'Teacher Dashboard - Tuition Hub')

@php
    $pageTitle = 'Teacher Dashboard';
    $pageSubtitle = 'Overview';

    $subscriptionName =
        $activeSubscription
            ?->snapshotPlanName()
        ?? $activeSubscription
            ?->plan
            ?->name
        ?? 'Active Plan';

    $subscriptionLimit =
        $activeSubscription
            ?->snapshotApplicationLimit();
@endphp

@section('content')

    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-6 text-white shadow-xl shadow-slate-200 sm:p-8">

        <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-indigo-500/20 blur-3xl"></div>
        <div class="absolute -bottom-20 right-44 h-60 w-60 rounded-full bg-violet-500/10 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">

            <div>

                <span class="inline-flex rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs font-medium text-slate-200">
                    Teacher Marketplace
                </span>

                <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">
                    Welcome back, {{ $user->name }}
                </h2>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300 sm:text-base">
                    Discover tuition opportunities, manage applications and respond to direct student requests.
                </p>

            </div>

            <div class="flex flex-wrap gap-3">

                <a
                    href="{{ route('teacher.tuitions.index') }}"
                    class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-900 transition hover:bg-slate-100"
                >
                    Find Tuition
                </a>

                <a
                    href="{{ route('teacher.requests.index') }}"
                    class="rounded-xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/15"
                >
                    Incoming Requests
                </a>

            </div>

        </div>

    </section>

    @if(! $activeSubscription)

        <section class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="font-bold text-amber-900">
                        Activate your subscription
                    </p>

                    <p class="mt-1 text-sm text-amber-700">
                        An active subscription is required before applying for tuition.
                    </p>

                </div>

                <a
                    href="{{ route('teacher.subscription.index') }}"
                    class="inline-flex justify-center rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700"
                >
                    View Plans
                </a>

            </div>

        </section>

    @else

        <section class="mt-6 rounded-2xl border border-indigo-100 bg-indigo-50 p-5">

            <div class="grid gap-5 sm:grid-cols-3">

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500">
                        Current Plan
                    </p>

                    <p class="mt-2 font-bold text-indigo-950">
                        {{ $subscriptionName }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500">
                        Applications
                    </p>

                    <p class="mt-2 font-bold text-indigo-950">

                        @if($subscriptionLimit === null)
                            Unlimited
                        @else
                            {{
                                $activeSubscription->applications_used
                            }}
                            /
                            {{ $subscriptionLimit }}
                        @endif

                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500">
                        Expires
                    </p>

                    <p class="mt-2 font-bold text-indigo-950">
                        {{
                            $activeSubscription
                                ->expires_at
                                ?->format('d M Y')
                            ?? 'No expiry'
                        }}
                    </p>
                </div>

            </div>

        </section>

    @endif

    @if(
        ! $teacherProfile ||
        ! $teacherProfile->university ||
        $teacherProfile->subjects->isEmpty()
    )

        <section class="mt-6 rounded-2xl border border-amber-200 bg-white p-5 shadow-sm">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="font-bold text-slate-900">
                        Complete your teacher profile
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Add education, subjects and preferred locations for better tuition matching.
                    </p>

                </div>

                <a
                    href="{{ route('teacher.profile.edit') }}"
                    class="inline-flex justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                >
                    Complete Profile
                </a>

            </div>

        </section>

    @endif

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">

        @php
            $stats = [
                [
                    'label' => 'Recommended Tuition',
                    'value' => $recommendedCount,
                ],
                [
                    'label' => 'Applications',
                    'value' => $applicationsCount,
                ],
                [
                    'label' => 'Shortlisted',
                    'value' => $shortlistedCount,
                ],
                [
                    'label' => 'Accepted Tuition',
                    'value' => $acceptedCount,
                ],
                [
                    'label' => 'Incoming Requests',
                    'value' => $incomingRequestCount,
                ],
            ];
        @endphp

        @foreach($stats as $stat)

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <p class="text-sm font-medium text-slate-500">
                    {{ $stat['label'] }}
                </p>

                <p class="mt-4 text-3xl font-black tracking-tight">
                    {{ number_format($stat['value']) }}
                </p>

            </div>

        @endforeach

    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">

        <a
            href="{{ route('teacher.tuitions.index') }}"
            class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5 transition hover:bg-indigo-100"
        >
            <p class="font-bold text-indigo-950">
                Find Tuition
            </p>

            <p class="mt-2 text-sm text-indigo-700">
                Browse available tuition opportunities.
            </p>
        </a>

        <a
            href="{{ route('teacher.tuitions.applications') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-indigo-200 hover:shadow-sm"
        >
            <p class="font-bold">
                My Applications
            </p>

            <p class="mt-2 text-sm text-slate-500">
                Track all submitted applications.
            </p>
        </a>

        <a
            href="{{ route('teacher.requests.index') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-indigo-200 hover:shadow-sm"
        >
            <p class="font-bold">
                Incoming Requests
            </p>

            <p class="mt-2 text-sm text-slate-500">
                Review direct requests from students.
            </p>
        </a>

        <a
            href="{{ route('teacher.subscription.index') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-indigo-200 hover:shadow-sm"
        >
            <p class="font-bold">
                Subscription
            </p>

            <p class="mt-2 text-sm text-slate-500">
                Manage your current plan and usage.
            </p>
        </a>

    </section>

    <section class="mt-8">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="text-xl font-bold">
                    Recent Tuition Posts
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Latest opportunities available to you.
                </p>

            </div>

            <a
                href="{{ route('teacher.tuitions.index') }}"
                class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
            >
                View all
            </a>

        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">

            @forelse($recentTuitions as $tuition)

                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">

                            <h3 class="line-clamp-2 font-bold text-slate-900">
                                {{ $tuition->title }}
                            </h3>

                            <p class="mt-1 text-xs font-medium text-slate-400">
                                {{ $tuition->tuition_code }}
                            </p>

                        </div>

                        <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">
                            OPEN
                        </span>

                    </div>

                    <div class="mt-5 space-y-3 text-sm text-slate-600">

                        <div class="flex justify-between gap-4">
                            <span>Class</span>
                            <span class="font-semibold text-slate-800">
                                {{ $tuition->class_level }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span>Location</span>
                            <span class="text-right font-semibold text-slate-800">
                                {{
                                    $tuition->location?->area
                                    ?? 'Not specified'
                                }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span>Salary</span>
                            <span class="font-semibold text-slate-800">
                                ৳{{ number_format((float) $tuition->salary) }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span>Subjects</span>
                            <span class="max-w-[60%] text-right font-semibold text-slate-800">
                                {{
                                    $tuition
                                        ->subjects
                                        ->pluck('name')
                                        ->join(', ')
                                }}
                            </span>
                        </div>

                    </div>

                    <a
                        href="{{ route(
                            'teacher.tuitions.show',
                            $tuition
                        ) }}"
                        class="mt-5 flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-600"
                    >
                        View Details
                    </a>

                </article>

            @empty

                <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">

                    <p class="font-semibold text-slate-700">
                        No tuition available
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        New tuition opportunities will appear here.
                    </p>

                </div>

            @endforelse

        </div>

    </section>

@endsection