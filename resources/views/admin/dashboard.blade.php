@extends('layouts.app')

@section('title', 'Admin Dashboard - Tuition Hub')

@php
    $pageTitle = 'Admin Dashboard';
    $pageSubtitle = 'Administration';
@endphp

@section('content')

    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-6 text-white shadow-xl shadow-slate-200 sm:p-8">

        <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-indigo-500/20 blur-3xl"></div>
        <div class="absolute -bottom-24 right-32 h-64 w-64 rounded-full bg-violet-500/10 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">

            <div>

                <span class="inline-flex rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs font-medium text-slate-200">
                    Platform Overview
                </span>

                <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">
                    Welcome back, {{ auth()->user()->name }}
                </h2>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300 sm:text-base">
                    Manage users, teacher verification, tuition activity,
                    payments, subscriptions and platform operations from one place.
                </p>

            </div>

            <div class="flex flex-wrap gap-3">

                <a
                    href="{{ route('admin.users.index') }}"
                    class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-900 transition hover:bg-slate-100"
                >
                    Manage Users
                </a>

                <a
                    href="{{ route(
                        'admin.teachers.index',
                        ['status' => 'pending']
                    ) }}"
                    class="rounded-xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/15"
                >
                    Review Teachers
                </a>

            </div>

        </div>

    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <a
            href="{{ route(
                'admin.users.index',
                ['role' => 'teacher']
            ) }}"
            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow-md"
        >
            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-sm font-medium text-slate-500">
                        Total Teachers
                    </p>

                    <p class="mt-4 text-3xl font-black tracking-tight text-slate-900">
                        {{ number_format($totalTeachers) }}
                    </p>

                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 21a7.5 7.5 0 0 1 15 0"
                        />
                    </svg>
                </div>

            </div>

            <p class="mt-5 text-sm font-semibold text-indigo-600 group-hover:text-indigo-700">
                View all teachers →
            </p>
        </a>

        <a
            href="{{ route(
                'admin.users.index',
                ['role' => 'student']
            ) }}"
            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-violet-200 hover:shadow-md"
        >
            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-sm font-medium text-slate-500">
                        Students / Guardians
                    </p>

                    <p class="mt-4 text-3xl font-black tracking-tight text-slate-900">
                        {{ number_format($totalStudents) }}
                    </p>

                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72M18 18.72A9.1 9.1 0 0 1 12 21c-2.305 0-4.41-.857-6-2.27m12 0v-.94c0-.793-.134-1.555-.38-2.265m-11.24 0A5.381 5.381 0 0 1 12 11.625a5.381 5.381 0 0 1 5.62 3.89M15.75 5.625a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
                        />
                    </svg>
                </div>

            </div>

            <p class="mt-5 text-sm font-semibold text-violet-600">
                Manage accounts →
            </p>
        </a>

        <a
            href="{{ route('admin.tuitions.index') }}"
            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-200 hover:shadow-md"
        >
            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-sm font-medium text-slate-500">
                        Published Tuition
                    </p>

                    <p class="mt-4 text-3xl font-black tracking-tight text-slate-900">
                        {{ number_format($publishedTuitions) }}
                    </p>

                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"
                        />
                    </svg>
                </div>

            </div>

            <p class="mt-5 text-sm font-semibold text-emerald-600">
                Review tuitions →
            </p>
        </a>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-sm font-medium text-slate-500">
                        Applications
                    </p>

                    <p class="mt-4 text-3xl font-black tracking-tight text-slate-900">
                        {{ number_format($totalApplications) }}
                    </p>

                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 5.25H6.75A2.25 2.25 0 0 0 4.5 7.5v12a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-12a2.25 2.25 0 0 0-2.25-2.25H15M9 5.25a3 3 0 0 1 6 0M9 5.25A3 3 0 0 0 12 8.25a3 3 0 0 0 3-3m-6 6h6m-6 3h4.5"
                        />
                    </svg>
                </div>

            </div>

            <p class="mt-5 text-sm text-slate-500">
                Total teacher applications across the platform.
            </p>

        </div>

    </section>

    <section class="mt-7 grid gap-5 lg:grid-cols-3">

        <div class="lg:col-span-2">

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <h3 class="font-bold text-slate-900">
                            Platform Management
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Quick access to the main administrative modules.
                        </p>

                    </div>

                </div>

                <div class="grid gap-3 p-4 sm:grid-cols-2">

                    <a
                        href="{{ route('admin.users.index') }}"
                        class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/50"
                    >
                        <p class="font-semibold text-slate-900">
                            User Management
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Activate, suspend and manage platform users.
                        </p>
                    </a>

                    <a
                        href="{{ route('admin.teachers.index') }}"
                        class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/50"
                    >
                        <p class="font-semibold text-slate-900">
                            Teacher Verification
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Review teacher verification status.
                        </p>
                    </a>

                    <a
                        href="{{ route('admin.tuitions.index') }}"
                        class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/50"
                    >
                        <p class="font-semibold text-slate-900">
                            Tuition Moderation
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Publish, return or close tuition posts.
                        </p>
                    </a>

                    <a
                        href="{{ route('admin.assignments.index') }}"
                        class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/50"
                    >
                        <p class="font-semibold text-slate-900">
                            Assignments
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Monitor confirmed tuition assignments.
                        </p>
                    </a>

                    <a
                        href="{{ route('admin.payments.index') }}"
                        class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/50"
                    >
                        <p class="font-semibold text-slate-900">
                            Payments
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Review and process subscription payments.
                        </p>
                    </a>

                    <a
                        href="{{ route('admin.complaints.index') }}"
                        class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/50"
                    >
                        <p class="font-semibold text-slate-900">
                            Complaints
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Investigate and resolve user complaints.
                        </p>
                    </a>

                </div>

            </div>

        </div>

        <div class="space-y-5">

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <h3 class="font-bold text-slate-900">
                    Subscription Management
                </h3>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Manage plans, pricing and payment configuration.
                </p>

                <div class="mt-5 space-y-2">

                    <a
                        href="{{ route('admin.subscription-plans.index') }}"
                        class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700"
                    >
                        <span>Subscription Plans</span>
                        <span>→</span>
                    </a>

                    <a
                        href="{{ route('admin.payment-settings.edit') }}"
                        class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700"
                    >
                        <span>Payment Settings</span>
                        <span>→</span>
                    </a>

                </div>

            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <h3 class="font-bold text-slate-900">
                    System Monitoring
                </h3>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Review platform activity and administrative history.
                </p>

                <a
                    href="{{ route('admin.audit-logs.index') }}"
                    class="mt-5 flex items-center justify-between rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-600"
                >
                    <span>View Audit Logs</span>
                    <span>→</span>
                </a>

            </div>

        </div>

    </section>

@endsection