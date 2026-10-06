@extends('layouts.app')

@section('title', 'My Complaints - Tuition Hub')

@php
    $pageTitle = 'My Complaints';
    $pageSubtitle = 'Student Support';
@endphp

@section('content')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <h2 class="text-2xl font-black text-slate-900">
                My Complaints
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Track complaint status and admin responses.
            </p>

        </div>

        <a
            href="{{ route('student.assignments.index') }}"
            class="inline-flex justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
        >
            Back to Assignments
        </a>

    </div>

    <div class="mt-6 space-y-4">

        @forelse($complaints as $complaint)

            @php
                $statusClasses = match($complaint->status) {
                    'open' => 'bg-amber-50 text-amber-700',
                    'investigating' => 'bg-indigo-50 text-indigo-700',
                    'resolved' => 'bg-emerald-50 text-emerald-700',
                    'rejected' => 'bg-rose-50 text-rose-700',
                    default => 'bg-slate-100 text-slate-600',
                };
            @endphp

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                    <div>

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Complaint #{{ $complaint->id }}
                        </p>

                        <h3 class="mt-2 text-lg font-bold text-slate-900">
                            {{ $complaint->subject }}
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            Category:
                            <span class="font-semibold text-slate-700">
                                {{
                                    ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $complaint->category
                                        )
                                    )
                                }}
                            </span>
                        </p>

                    </div>

                    <span class="h-fit rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">
                        {{ strtoupper($complaint->status) }}
                    </span>

                </div>

                <p class="mt-5 whitespace-pre-line text-sm leading-6 text-slate-700">
                    {{ $complaint->description }}
                </p>

                @if($complaint->admin_note)

                    <div class="mt-5 rounded-2xl border border-indigo-100 bg-indigo-50 p-4">

                        <p class="text-sm font-bold text-indigo-900">
                            Admin Note
                        </p>

                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-indigo-800">
                            {{ $complaint->admin_note }}
                        </p>

                    </div>

                @endif

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

                <h3 class="font-bold text-slate-800">
                    No complaints submitted
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Any complaint you submit will appear here.
                </p>

            </div>

        @endforelse

    </div>

    @if($complaints->hasPages())

        <div class="mt-8">
            {{ $complaints->links() }}
        </div>

    @endif

@endsection