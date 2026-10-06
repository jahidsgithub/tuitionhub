@extends('layouts.app')

@section('title', 'My Applications - Tuition Hub')

@php
    $pageTitle = 'My Applications';
    $pageSubtitle = 'Teacher Marketplace';
@endphp

@section('content')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <h2 class="text-2xl font-black text-slate-900">
                My Applications
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Track your tuition applications and current status.
            </p>

        </div>

        <a
            href="{{ route('teacher.tuitions.index') }}"
            class="inline-flex justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
        >
            Find Tuition
        </a>

    </div>

    <div class="mt-6 space-y-4">

        @forelse($applications as $application)

            @php
                $statusClasses = match($application->status) {
                    'accepted' => 'bg-emerald-50 text-emerald-700',
                    'rejected' => 'bg-rose-50 text-rose-700',
                    'shortlisted' => 'bg-indigo-50 text-indigo-700',
                    default => 'bg-amber-50 text-amber-700',
                };

                $tuition = $application->tuitionPost;
            @endphp

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">

                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-2">

                            <h3 class="text-lg font-bold text-slate-900">
                                {{ $tuition?->title ?? 'Tuition' }}
                            </h3>

                            <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $statusClasses }}">
                                {{ strtoupper($application->status) }}
                            </span>

                        </div>

                        <p class="mt-2 text-xs font-medium text-slate-400">
                            {{ $tuition?->tuition_code ?? 'N/A' }}
                        </p>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                            <div>

                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Class
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $tuition?->class_level ?? 'N/A' }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Location
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $tuition?->location?->area ?? 'Not specified' }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Tuition Salary
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">

                                    @if($tuition?->salary !== null)

                                        ৳{{ number_format(
                                            (float) $tuition->salary
                                        ) }}

                                    @else

                                        Negotiable

                                    @endif

                                </p>

                            </div>

                            <div>

                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Your Expected Salary
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">

                                    @if($application->expected_salary)

                                        ৳{{ number_format(
                                            (float) $application->expected_salary
                                        ) }}

                                    @else

                                        Not specified

                                    @endif

                                </p>

                            </div>

                        </div>

                        @if($application->message)

                            <div class="mt-5 rounded-xl bg-slate-50 p-4">

                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Your Message
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">
                                    {{ $application->message }}
                                </p>

                            </div>

                        @endif

                    </div>

                    @if(
                        $tuition &&
                        $tuition->status === 'published'
                    )

                        <a
                            href="{{ route(
                                'teacher.tuitions.show',
                                $tuition
                            ) }}"
                            class="inline-flex shrink-0 justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            View Tuition
                        </a>

                    @endif

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

                <h3 class="text-xl font-bold text-slate-900">
                    No applications yet
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Browse available tuition opportunities and submit your first application.
                </p>

                <a
                    href="{{ route('teacher.tuitions.index') }}"
                    class="mt-5 inline-flex rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white"
                >
                    Find Tuition
                </a>

            </div>

        @endforelse

    </div>

    @if(
        method_exists($applications, 'hasPages') &&
        $applications->hasPages()
    )

        <div class="mt-8">
            {{ $applications->links() }}
        </div>

    @endif

@endsection