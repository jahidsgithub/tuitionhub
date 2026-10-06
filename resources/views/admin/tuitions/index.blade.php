@extends('layouts.app')

@section('title', 'Tuition Moderation - Tuition Hub Admin')

@php
    $pageTitle = 'Tuition Moderation';
    $pageSubtitle = 'Admin Panel';
@endphp

@section('content')

    <div>

        <h2 class="text-2xl font-black text-slate-900">
            Tuition Moderation
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Review and manage tuition posts across the platform.
        </p>

    </div>

    <form
        method="GET"
        action="{{ route('admin.tuitions.index') }}"
        class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >

        <div class="grid gap-4 md:grid-cols-4">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search title, ID, student..."
                class="rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >

            <select
                name="status"
                class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >
                <option value="">
                    All Statuses
                </option>

                @foreach([
                    'draft' => 'Draft',
                    'pending' => 'Pending',
                    'published' => 'Published',
                    'filled' => 'Filled',
                    'closed' => 'Closed',
                ] as $value => $label)

                    <option
                        value="{{ $value }}"
                        @selected(request('status') === $value)
                    >
                        {{ $label }}
                    </option>

                @endforeach
            </select>

            <select
                name="teaching_mode"
                class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
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

            <div class="flex gap-2">

                <button
                    type="submit"
                    class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Filter
                </button>

                @if(
                    request()->filled('search') ||
                    request()->filled('status') ||
                    request()->filled('teaching_mode')
                )

                    <a
                        href="{{ route('admin.tuitions.index') }}"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600"
                    >
                        Clear
                    </a>

                @endif

            </div>

        </div>

    </form>

    <div class="mt-6 space-y-5">

        @forelse($tuitions as $tuition)

            @php
                $statusClass = match($tuition->status) {
                    'published' => 'bg-emerald-50 text-emerald-700',
                    'pending' => 'bg-amber-50 text-amber-700',
                    'filled' => 'bg-indigo-50 text-indigo-700',
                    'closed' => 'bg-slate-100 text-slate-600',
                    default => 'bg-slate-100 text-slate-600',
                };
            @endphp

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <div class="flex flex-col gap-6 lg:flex-row lg:justify-between">

                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-2">

                            <h3 class="text-xl font-bold text-slate-900">
                                {{ $tuition->title }}
                            </h3>

                            <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $statusClass }}">
                                {{ strtoupper($tuition->status) }}
                            </span>

                        </div>

                        <p class="mt-2 text-sm text-slate-500">
                            Tuition ID:
                            <strong class="text-slate-700">
                                {{ $tuition->tuition_code }}
                            </strong>
                        </p>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                            <div>

                                <p class="text-xs text-slate-400">
                                    Student / Guardian
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $tuition->user?->name ?? 'N/A' }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs text-slate-400">
                                    Email
                                </p>

                                <p class="mt-1 break-all text-sm font-semibold text-slate-800">
                                    {{ $tuition->user?->email ?? 'N/A' }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs text-slate-400">
                                    Phone
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $tuition->user?->phone ?? 'N/A' }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs text-slate-400">
                                    Class
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $tuition->class_level }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs text-slate-400">
                                    Location
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $tuition->location?->area ?? 'Not specified' }}
                                </p>

                            </div>

                            <div>

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

                            <div>

                                <p class="text-xs text-slate-400">
                                    Mode
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ ucfirst($tuition->teaching_mode) }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs text-slate-400">
                                    Applications
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $tuition->applications_count }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs text-slate-400">
                                    Posted
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $tuition->created_at->format('d M Y') }}
                                </p>

                            </div>

                        </div>

                        <div class="mt-5">

                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Subjects
                            </p>

                            <div class="mt-2 flex flex-wrap gap-2">

                                @forelse($tuition->subjects as $subject)

                                    <span class="rounded-lg bg-indigo-50 px-3 py-1 text-sm text-indigo-700">
                                        {{ $subject->name }}
                                    </span>

                                @empty

                                    <span class="text-sm text-slate-500">
                                        No subjects selected.
                                    </span>

                                @endforelse

                            </div>

                        </div>

                        @if($tuition->requirements)

                            <div class="mt-5 rounded-xl bg-slate-50 p-4">

                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Requirements
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                                    {{ $tuition->requirements }}
                                </p>

                            </div>

                        @endif

                    </div>

                    <div class="space-y-3 lg:w-52">

                        @if(
                            ! in_array(
                                $tuition->status,
                                ['published', 'filled'],
                                true
                            )
                        )

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.tuitions.publish',
                                    $tuition
                                ) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Publish this tuition?')"
                                    class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700"
                                >
                                    Publish
                                </button>

                            </form>

                        @endif

                        @if(
                            ! in_array(
                                $tuition->status,
                                ['pending', 'filled'],
                                true
                            )
                        )

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.tuitions.pending',
                                    $tuition
                                ) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Move this tuition to pending?')"
                                    class="w-full rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-amber-600"
                                >
                                    Move to Pending
                                </button>

                            </form>

                        @endif

                        @if(
                            ! in_array(
                                $tuition->status,
                                ['closed', 'filled'],
                                true
                            )
                        )

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.tuitions.close',
                                    $tuition
                                ) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Close this tuition?')"
                                    class="w-full rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                                >
                                    Close
                                </button>

                            </form>

                        @endif

                        @if($tuition->status === 'filled')

                            <div class="rounded-xl bg-indigo-50 p-4 text-center text-sm font-bold text-indigo-700">
                                Tuition Filled
                            </div>

                        @endif

                    </div>

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center text-sm text-slate-500">
                No tuition posts found.
            </div>

        @endforelse

    </div>

    @if($tuitions->hasPages())

        <div class="mt-8">
            {{ $tuitions->links() }}
        </div>

    @endif

@endsection