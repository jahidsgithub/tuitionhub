@extends('layouts.app')

@section('title', 'Student Dashboard - Tuition Hub')

@php
    $pageTitle = 'Student Dashboard';
    $pageSubtitle = 'Overview';
@endphp

@section('content')

    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 p-6 text-white shadow-xl shadow-indigo-100 sm:p-8">

        <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -bottom-24 right-40 h-56 w-56 rounded-full bg-violet-300/20 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">

            <div>

                <div class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-medium text-indigo-100">
                    Student / Guardian
                </div>

                <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">
                    Welcome back, {{ $user->name }}
                </h2>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-indigo-100 sm:text-base">
                    Find verified teachers, post tuition requirements and manage your tuition journey from one place.
                </p>

            </div>

            <div class="flex flex-wrap gap-3">

                <a
                    href="{{ route('student.teachers.index') }}"
                    class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-indigo-700 shadow-sm transition hover:bg-indigo-50"
                >
                    Find Teachers
                </a>

                @if(Route::has('student.tuitions.create'))
                    <a
                        href="{{ route('student.tuitions.create') }}"
                        class="rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/20"
                    >
                        Post Tuition
                    </a>
                @endif

            </div>

        </div>

    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @php
            $stats = [
                [
                    'label' => 'Active Tuition Posts',
                    'value' => $activeTuitionCount,
                    'tone' => 'indigo',
                ],
                [
                    'label' => 'Teacher Applications',
                    'value' => $applicationsCount,
                    'tone' => 'violet',
                ],
                [
                    'label' => 'Shortlisted Teachers',
                    'value' => $shortlistedCount,
                    'tone' => 'amber',
                ],
                [
                    'label' => 'Filled Tuition',
                    'value' => $filledTuitionCount,
                    'tone' => 'emerald',
                ],
            ];
        @endphp

        @foreach($stats as $stat)

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-center justify-between">

                    <p class="text-sm font-medium text-slate-500">
                        {{ $stat['label'] }}
                    </p>

                    <span class="h-2.5 w-2.5 rounded-full
                        {{
                            match($stat['tone']) {
                                'violet' => 'bg-violet-500',
                                'amber' => 'bg-amber-500',
                                'emerald' => 'bg-emerald-500',
                                default => 'bg-indigo-500',
                            }
                        }}"
                    ></span>

                </div>

                <p class="mt-4 text-3xl font-black tracking-tight text-slate-900">
                    {{ number_format($stat['value']) }}
                </p>

            </div>

        @endforeach

    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">

        <a
            href="{{ route('student.teachers.index') }}"
            class="group rounded-2xl border border-indigo-100 bg-indigo-50 p-5 transition hover:border-indigo-200 hover:bg-indigo-100"
        >
            <p class="font-bold text-indigo-900">
                Find Teachers
            </p>

            <p class="mt-2 text-sm leading-6 text-indigo-700">
                Search verified and available teachers.
            </p>

            <p class="mt-4 text-sm font-semibold text-indigo-700">
                Browse teachers →
            </p>
        </a>

        <a
            href="{{ route('student.teacher-requests.index') }}"
            class="group rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-indigo-200 hover:shadow-sm"
        >
            <p class="font-bold text-slate-900">
                Teacher Requests
            </p>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Track direct teacher requests and responses.
            </p>

            <p class="mt-4 text-sm font-semibold text-indigo-600">
                View requests →
            </p>
        </a>

        @if(Route::has('student.tuitions.create'))

            <a
                href="{{ route('student.tuitions.create') }}"
                class="group rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-indigo-200 hover:shadow-sm"
            >
                <p class="font-bold text-slate-900">
                    Post New Tuition
                </p>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Create a new tuition requirement.
                </p>

                <p class="mt-4 text-sm font-semibold text-indigo-600">
                    Create post →
                </p>
            </a>

        @endif

        <a
            href="{{ route('student.tuitions.index') }}"
            class="group rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-indigo-200 hover:shadow-sm"
        >
            <p class="font-bold text-slate-900">
                Manage Tuitions
            </p>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Review posts, applications and progress.
            </p>

            <p class="mt-4 text-sm font-semibold text-indigo-600">
                Manage posts →
            </p>
        </a>

    </section>

    <section class="mt-8">

        <div class="flex items-center justify-between gap-4">

            <div>
                <h2 class="text-xl font-bold text-slate-900">
                    Recent Tuition Posts
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Your latest tuition requirements.
                </p>
            </div>

            <a
                href="{{ route('student.tuitions.index') }}"
                class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
            >
                View all
            </a>

        </div>

        <div class="mt-4 space-y-3">

            @forelse($recentTuitions as $tuition)

                @php
                    $statusClasses = match($tuition->status) {
                        'published' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
                        'filled' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/10',
                        'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/10',
                        'closed' => 'bg-slate-100 text-slate-600 ring-slate-500/10',
                        default => 'bg-slate-100 text-slate-600 ring-slate-500/10',
                    };
                @endphp

                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">

                        <div class="min-w-0">

                            <div class="flex flex-wrap items-center gap-2">

                                <h3 class="truncate font-bold text-slate-900">
                                    {{ $tuition->title }}
                                </h3>

                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase ring-1 ring-inset {{ $statusClasses }}">
                                    {{ $tuition->status }}
                                </span>

                            </div>

                            <p class="mt-1 text-xs font-medium text-slate-400">
                                {{ $tuition->tuition_code }}
                            </p>

                            <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-600">

                                <span>
                                    {{ $tuition->class_level }}
                                </span>

                                <span>
                                    {{ $tuition->location?->area ?? 'Location not specified' }}
                                </span>

                                <span class="font-semibold text-slate-800">
                                    ৳{{ number_format((float) $tuition->salary) }}
                                </span>

                            </div>

                        </div>

                        <a
                            href="{{ route(
                                'student.tuitions.applications.index',
                                $tuition
                            ) }}"
                            class="inline-flex shrink-0 items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                        >
                            Applications
                            <span class="ml-1.5 rounded-full bg-white/15 px-2 py-0.5 text-xs">
                                {{ $tuition->applications_count }}
                            </span>
                        </a>

                    </div>

                </article>

            @empty

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">

                    <p class="font-semibold text-slate-700">
                        No tuition posts yet
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        Create your first tuition requirement to start receiving teacher applications.
                    </p>

                </div>

            @endforelse

        </div>

    </section>

@endsection