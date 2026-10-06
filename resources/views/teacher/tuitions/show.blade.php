@extends('layouts.app')

@section('title', $tuition->title.' - Tuition Hub')

@php
    $pageTitle = 'Tuition Details';
    $pageSubtitle = $tuition->tuition_code;

    $planName =
        $activeSubscription?->plan_name_snapshot
        ?: $activeSubscription?->plan?->name
        ?: 'Active Plan';

    $applicationLimit = $activeSubscription
        ? (
            $activeSubscription->plan_snapshot_captured_at
                ? $activeSubscription->application_limit_snapshot
                : $activeSubscription->plan?->application_limit
        )
        : null;
@endphp

@section('content')

    <div class="mx-auto max-w-5xl">

        <div class="mb-5">

            <a
                href="{{ route('teacher.tuitions.index') }}"
                class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
            >
                ← Back to Find Tuition
            </a>

        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                <div>

                    <div class="flex flex-wrap items-center gap-2">

                        <h2 class="text-2xl font-black text-slate-900 sm:text-3xl">
                            {{ $tuition->title }}
                        </h2>

                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                            PUBLISHED
                        </span>

                    </div>

                    <p class="mt-2 text-sm text-slate-500">
                        Tuition ID: {{ $tuition->tuition_code }}
                    </p>

                </div>

            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                <div class="rounded-2xl bg-slate-50 p-4">

                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Class
                    </p>

                    <p class="mt-2 font-semibold text-slate-800">
                        {{ $tuition->class_level }}
                    </p>

                </div>

                <div class="rounded-2xl bg-slate-50 p-4">

                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Location
                    </p>

                    <p class="mt-2 font-semibold text-slate-800">
                        {{ $tuition->location?->area ?? 'Not specified' }}
                    </p>

                </div>

                <div class="rounded-2xl bg-slate-50 p-4">

                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Salary
                    </p>

                    <p class="mt-2 font-semibold text-slate-800">

                        @if($tuition->salary !== null)

                            ৳{{ number_format(
                                (float) $tuition->salary
                            ) }}/month

                        @else

                            Negotiable

                        @endif

                    </p>

                </div>

                <div class="rounded-2xl bg-slate-50 p-4">

                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Days / Week
                    </p>

                    <p class="mt-2 font-semibold text-slate-800">
                        {{ $tuition->days_per_week ?? 'Not specified' }}
                    </p>

                </div>

                <div class="rounded-2xl bg-slate-50 p-4">

                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Teaching Mode
                    </p>

                    <p class="mt-2 font-semibold text-slate-800">
                        {{ ucfirst($tuition->teaching_mode) }}
                    </p>

                </div>

                <div class="rounded-2xl bg-slate-50 p-4">

                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Preferred Teacher
                    </p>

                    <p class="mt-2 font-semibold text-slate-800">
                        {{ ucfirst($tuition->preferred_teacher_gender) }}
                    </p>

                </div>

            </div>

            <div class="mt-7">

                <h3 class="font-bold text-slate-900">
                    Subjects
                </h3>

                <div class="mt-3 flex flex-wrap gap-2">

                    @foreach($tuition->subjects as $subject)

                        <span class="rounded-xl bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700">
                            {{ $subject->name }}
                        </span>

                    @endforeach

                </div>

            </div>

            @if($tuition->requirements)

                <div class="mt-7">

                    <h3 class="font-bold text-slate-900">
                        Additional Requirements
                    </h3>

                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">
                        {{ $tuition->requirements }}
                    </p>

                </div>

            @endif

        </section>

        <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <h2 class="text-xl font-black text-slate-900">
                Apply for this Tuition
            </h2>

            <p class="mt-2 text-sm text-slate-500">
                Submit your expected salary and a short message to the student.
            </p>

            @if($alreadyApplied)

                <div class="mt-5 rounded-2xl border border-indigo-200 bg-indigo-50 p-4 text-sm font-medium text-indigo-700">
                    You have already applied for this tuition.
                </div>

            @elseif(! $activeSubscription)

                <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5">

                    <h3 class="font-bold text-amber-900">
                        Subscription Required
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-amber-700">
                        Activate a subscription plan before applying for tuition.
                    </p>

                    <a
                        href="{{ route('teacher.subscription.index') }}"
                        class="mt-4 inline-flex rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white"
                    >
                        View Subscription Plans
                    </a>

                </div>

            @elseif(! $activeSubscription->canApply())

                <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 p-5">

                    <h3 class="font-bold text-rose-900">
                        Application Limit Reached
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-rose-700">
                        You have used all applications included in your current plan.
                    </p>

                    <a
                        href="{{ route('teacher.subscription.index') }}"
                        class="mt-4 inline-flex rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white"
                    >
                        View Subscription
                    </a>

                </div>

            @else

                <div class="mt-5 rounded-2xl border border-indigo-100 bg-indigo-50 p-4">

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                        <p class="text-sm text-indigo-700">
                            Current Plan:
                            <strong>{{ $planName }}</strong>
                        </p>

                        <p class="text-sm text-indigo-700">

                            @if($applicationLimit === null)

                                Unlimited applications available

                            @else

                                {{ $activeSubscription->applications_used }}
                                /
                                {{ $applicationLimit }}
                                applications used

                            @endif

                        </p>

                    </div>

                </div>

                <form
                    method="POST"
                    action="{{ route(
                        'teacher.tuitions.apply',
                        $tuition
                    ) }}"
                    class="mt-6 space-y-5"
                >
                    @csrf

                    <div>

                        <label
                            for="expected_salary"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Expected Salary
                        </label>

                        <div class="relative">

                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-500">
                                ৳
                            </span>

                            <input
                                id="expected_salary"
                                type="number"
                                min="0"
                                name="expected_salary"
                                value="{{ old(
                                    'expected_salary',
                                    $tuition->salary
                                ) }}"
                                class="w-full rounded-xl border border-slate-300 py-3 pl-9 pr-4 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >

                        </div>

                    </div>

                    <div>

                        <label
                            for="message"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Message
                        </label>

                        <textarea
                            id="message"
                            name="message"
                            rows="5"
                            placeholder="Write a short message about why you are suitable for this tuition..."
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >{{ old('message') }}</textarea>

                    </div>

                    <button
                        type="submit"
                        class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-100 transition hover:bg-indigo-700"
                    >
                        Submit Application
                    </button>

                </form>

            @endif

        </section>

    </div>

@endsection