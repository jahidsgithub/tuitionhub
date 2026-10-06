@extends('layouts.app')

@section('title', 'Complaints - Tuition Hub Admin')

@php
    $pageTitle = 'Complaint Management';
    $pageSubtitle = 'Admin Panel';
@endphp

@section('content')

    <div>
        <h2 class="text-2xl font-black text-slate-900">
            Complaint Management
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Review and resolve platform complaints.
        </p>
    </div>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Open</p>

            <p class="mt-2 text-3xl font-black text-amber-600">
                {{ $openCount }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">
                Investigating
            </p>

            <p class="mt-2 text-3xl font-black text-indigo-600">
                {{ $investigatingCount }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">
                Resolved
            </p>

            <p class="mt-2 text-3xl font-black text-emerald-600">
                {{ $resolvedCount }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">
                Rejected
            </p>

            <p class="mt-2 text-3xl font-black text-rose-600">
                {{ $rejectedCount }}
            </p>
        </div>

    </section>

    <form
        method="GET"
        action="{{ route('admin.complaints.index') }}"
        class="mt-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-4"
    >

        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Complaint ID / user / subject"
            class="rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
        >

        <select
            name="status"
            class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
        >
            <option value="">All Status</option>

            @foreach([
                'open' => 'Open',
                'investigating' => 'Investigating',
                'resolved' => 'Resolved',
                'rejected' => 'Rejected',
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
            name="category"
            class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
        >
            <option value="">All Categories</option>

            @foreach([
                'behavior' => 'Behavior',
                'payment' => 'Payment',
                'attendance' => 'Attendance',
                'misinformation' => 'Misinformation',
                'harassment' => 'Harassment',
                'safety' => 'Safety',
                'other' => 'Other',
            ] as $value => $label)

                <option
                    value="{{ $value }}"
                    @selected(request('category') === $value)
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
                request()->filled('status') ||
                request()->filled('category')
            )

                <a
                    href="{{ route('admin.complaints.index') }}"
                    class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600"
                >
                    Clear
                </a>

            @endif

        </div>

    </form>

    <div class="mt-6 space-y-5">

        @forelse($complaints as $complaint)

            @php
                $statusClass = match($complaint->status) {
                    'open' => 'bg-amber-50 text-amber-700',
                    'investigating' => 'bg-indigo-50 text-indigo-700',
                    'resolved' => 'bg-emerald-50 text-emerald-700',
                    'rejected' => 'bg-rose-50 text-rose-700',
                    default => 'bg-slate-100 text-slate-600',
                };
            @endphp

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <div class="flex flex-col gap-6 lg:flex-row lg:justify-between">

                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-2">

                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Complaint #{{ $complaint->id }}
                            </p>

                            <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $statusClass }}">
                                {{ strtoupper($complaint->status) }}
                            </span>

                        </div>

                        <h3 class="mt-2 text-xl font-bold text-slate-900">
                            {{ $complaint->subject }}
                        </h3>

                        <div class="mt-4 space-y-2 text-sm text-slate-600">

                            <p>
                                <strong>Reporter:</strong>
                                {{ $complaint->reporter?->name ?? 'N/A' }}
                            </p>

                            <p>
                                <strong>Reported User:</strong>
                                {{ $complaint->reportedUser?->name ?? 'N/A' }}
                            </p>

                            <p>
                                <strong>Category:</strong>
                                {{
                                    ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $complaint->category
                                        )
                                    )
                                }}
                            </p>

                        </div>

                        <div class="mt-5 rounded-xl bg-slate-50 p-4">

                            <p class="whitespace-pre-line text-sm leading-6 text-slate-700">
                                {{ $complaint->description }}
                            </p>

                        </div>

                    </div>

                    <form
                        method="POST"
                        action="{{ route(
                            'admin.complaints.update',
                            $complaint
                        ) }}"
                        class="lg:w-96"
                    >
                        @csrf
                        @method('PATCH')

                        <label
                            for="status_{{ $complaint->id }}"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Status
                        </label>

                        <select
                            id="status_{{ $complaint->id }}"
                            name="status"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >
                            @foreach([
                                'open',
                                'investigating',
                                'resolved',
                                'rejected',
                            ] as $status)

                                <option
                                    value="{{ $status }}"
                                    @selected(
                                        $complaint->status === $status
                                    )
                                >
                                    {{ ucfirst($status) }}
                                </option>

                            @endforeach
                        </select>

                        <label
                            for="admin_note_{{ $complaint->id }}"
                            class="mb-2 mt-4 block text-sm font-semibold text-slate-700"
                        >
                            Admin Note
                        </label>

                        <textarea
                            id="admin_note_{{ $complaint->id }}"
                            name="admin_note"
                            rows="5"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >{{ $complaint->admin_note }}</textarea>

                        <button
                            type="submit"
                            class="mt-4 w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                        >
                            Update Complaint
                        </button>

                    </form>

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center text-sm text-slate-500">
                No complaints found.
            </div>

        @endforelse

    </div>

    @if($complaints->hasPages())
        <div class="mt-8">
            {{ $complaints->links() }}
        </div>
    @endif

@endsection