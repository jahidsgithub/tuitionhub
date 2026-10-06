@extends('layouts.app')

@section('title', 'Find Tuition - Tuition Hub')

@php
    $pageTitle = 'Find Tuition';
    $pageSubtitle = 'Teacher Marketplace';
@endphp

@section('content')

    <section class="rounded-3xl bg-gradient-to-br from-slate-950 via-indigo-950 to-indigo-700 p-6 text-white sm:p-8">

        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <p class="text-sm font-semibold text-indigo-200">
                    Tuition Marketplace
                </p>

                <h2 class="mt-2 text-3xl font-black">
                    Find your next tuition
                </h2>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-indigo-100">
                    Browse published tuition opportunities and apply to the ones
                    that match your experience and preferences.
                </p>

            </div>

            <a
                href="{{ route('teacher.tuitions.applications') }}"
                class="inline-flex justify-center rounded-xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/20"
            >
                My Applications
            </a>

        </div>

    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

        <form
            method="GET"
            action="{{ route('teacher.tuitions.index') }}"
            class="grid gap-4 md:grid-cols-[minmax(0,1fr)_220px_auto]"
        >

            <div>

                <label
                    for="search"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Search Tuition
                </label>

                <input
                    id="search"
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Title, class, subject or tuition code"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                >

            </div>

            <div>

                <label
                    for="teaching_mode"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Teaching Mode
                </label>

                <select
                    id="teaching_mode"
                    name="teaching_mode"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                >
                    <option value="">
                        All Modes
                    </option>

                    <option
                        value="offline"
                        @selected(request('teaching_mode') === 'offline')
                    >
                        Offline
                    </option>

                    <option
                        value="online"
                        @selected(request('teaching_mode') === 'online')
                    >
                        Online
                    </option>

                    <option
                        value="both"
                        @selected(request('teaching_mode') === 'both')
                    >
                        Both
                    </option>
                </select>

            </div>

            <div class="flex items-end gap-2">

                <button
                    type="submit"
                    class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Search
                </button>

                @if(
                    request()->filled('search') ||
                    request()->filled('teaching_mode')
                )

                    <a
                        href="{{ route('teacher.tuitions.index') }}"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                    >
                        Clear
                    </a>

                @endif

            </div>

        </form>

    </section>

    <section class="mt-6">

        <div class="mb-4 flex items-center justify-between">

            <div>

                <h3 class="text-xl font-black text-slate-900">
                    Available Tuitions
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $tuitions->total() }} opportunity(s) found
                </p>

            </div>

        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

            @forelse($tuitions as $tuition)

                <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow-md">

                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">

                            <h4 class="text-lg font-bold text-slate-900">
                                {{ $tuition->title }}
                            </h4>

                            <p class="mt-1 text-xs font-medium text-slate-400">
                                {{ $tuition->tuition_code }}
                            </p>

                        </div>

                        <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">
                            {{ strtoupper($tuition->status) }}
                        </span>

                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">

                        <div class="rounded-xl bg-slate-50 p-3">

                            <p class="text-xs text-slate-400">
                                Class
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $tuition->class_level }}
                            </p>

                        </div>

                        <div class="rounded-xl bg-slate-50 p-3">

                            <p class="text-xs text-slate-400">
                                Location
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $tuition->location?->area ?? 'Not specified' }}
                            </p>

                        </div>

                        <div class="rounded-xl bg-slate-50 p-3">

                            <p class="text-xs text-slate-400">
                                Salary
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">

                                @if($tuition->salary !== null)

                                    ৳{{ number_format(
                                        (float) $tuition->salary
                                    ) }}

                                @else

                                    Negotiable

                                @endif

                            </p>

                        </div>

                        <div class="rounded-xl bg-slate-50 p-3">

                            <p class="text-xs text-slate-400">
                                Mode
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{ ucfirst($tuition->teaching_mode) }}
                            </p>

                        </div>

                    </div>

                    <div class="mt-5">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Subjects
                        </p>

                        <div class="mt-2 flex flex-wrap gap-2">

                            @foreach($tuition->subjects->take(4) as $subject)

                                <span class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">
                                    {{ $subject->name }}
                                </span>

                            @endforeach

                            @if($tuition->subjects->count() > 4)

                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs text-slate-500">
                                    +{{ $tuition->subjects->count() - 4 }}
                                </span>

                            @endif

                        </div>

                    </div>

                    <div class="mt-5 text-sm text-slate-500">
                        {{ $tuition->days_per_week ?? 'N/A' }} day(s) / week
                    </div>

                    <div class="mt-auto pt-6">

                        <a
                            href="{{ route(
                                'teacher.tuitions.show',
                                $tuition
                            ) }}"
                            class="flex w-full justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-indigo-600"
                        >
                            View Details
                        </a>

                    </div>

                </article>

            @empty

                <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

                    <h3 class="text-lg font-bold text-slate-800">
                        No tuition found
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Try changing your search filters.
                    </p>

                </div>

            @endforelse

        </div>

        @if($tuitions->hasPages())

            <div class="mt-8">
                {{ $tuitions->links() }}
            </div>

        @endif

    </section>

@endsection