@extends('layouts.app')

@section('title', 'My Tuition Posts - Tuition Hub')

@php
    $pageTitle = 'My Tuition Posts';
    $pageSubtitle = 'Student Marketplace';
@endphp

@section('content')

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div>

            <h2 class="text-2xl font-black text-slate-900">
                My Tuition Posts
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Manage tuition requirements and review teacher applications.
            </p>

        </div>

        <a
            href="{{ route('student.tuitions.create') }}"
            class="inline-flex justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
        >
            + Post New Tuition
        </a>

    </div>

    <div class="mt-6 space-y-5">

        @forelse($tuitions as $tuition)

            @php
                $statusClasses = match($tuition->status) {
                    'published' => 'bg-emerald-50 text-emerald-700',
                    'filled' => 'bg-indigo-50 text-indigo-700',
                    'pending' => 'bg-amber-50 text-amber-700',
                    'closed' => 'bg-slate-100 text-slate-600',
                    default => 'bg-slate-100 text-slate-600',
                };

                $canEdit = ! in_array(
                    $tuition->status,
                    [
                        'filled',
                        'closed',
                    ],
                    true
                );

                $canClose = ! in_array(
                    $tuition->status,
                    [
                        'filled',
                        'closed',
                    ],
                    true
                );

                $canDelete =
                    (int) $tuition->applications_count === 0;
            @endphp

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="p-5 sm:p-6">

                    <div class="flex flex-col gap-6 lg:flex-row">

                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-2">

                                <h3 class="text-xl font-bold text-slate-900">
                                    {{ $tuition->title }}
                                </h3>

                                <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $statusClasses }}">
                                    {{ strtoupper($tuition->status) }}
                                </span>

                            </div>

                            <p class="mt-2 text-xs font-medium text-slate-400">
                                Tuition ID:
                                {{ $tuition->tuition_code }}
                            </p>

                            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">

                                <div>

                                    <p class="text-xs uppercase tracking-wide text-slate-400">
                                        Class
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-800">
                                        {{ $tuition->class_level }}
                                    </p>

                                </div>

                                <div>

                                    <p class="text-xs uppercase tracking-wide text-slate-400">
                                        Location
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-800">
                                        {{ $tuition->location?->area ?? 'Not specified' }}
                                    </p>

                                </div>

                                <div>

                                    <p class="text-xs uppercase tracking-wide text-slate-400">
                                        Salary
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-800">

                                        @if($tuition->salary !== null)

                                            ৳{{ number_format(
                                                (float) $tuition->salary
                                            ) }}/month

                                        @else

                                            Negotiable

                                        @endif

                                    </p>

                                </div>

                                <div>

                                    <p class="text-xs uppercase tracking-wide text-slate-400">
                                        Days / Week
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-800">
                                        {{ $tuition->days_per_week ?? 'Not specified' }}
                                    </p>

                                </div>

                                <div>

                                    <p class="text-xs uppercase tracking-wide text-slate-400">
                                        Mode
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-800">
                                        {{
                                            ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $tuition->teaching_mode
                                                )
                                            )
                                        }}
                                    </p>

                                </div>

                                <div>

                                    <p class="text-xs uppercase tracking-wide text-slate-400">
                                        Teacher Preference
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-800">
                                        {{
                                            ucfirst(
                                                $tuition->preferred_teacher_gender
                                            )
                                        }}
                                    </p>

                                </div>

                            </div>

                            <div class="mt-6">

                                <p class="text-sm font-semibold text-slate-700">
                                    Subjects
                                </p>

                                <div class="mt-2 flex flex-wrap gap-2">

                                    @forelse($tuition->subjects as $subject)

                                        <span class="rounded-lg bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700">
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

                                <div class="mt-6">

                                    <p class="text-sm font-semibold text-slate-700">
                                        Additional Requirements
                                    </p>

                                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">
                                        {{ $tuition->requirements }}
                                    </p>

                                </div>

                            @endif

                        </div>

                        <aside class="lg:w-64">

                            <div class="rounded-2xl bg-slate-50 p-5">

                                <p class="text-sm text-slate-500">
                                    Teacher Applications
                                </p>

                                <p class="mt-2 text-4xl font-black text-slate-900">
                                    {{ $tuition->applications_count }}
                                </p>

                                <a
                                    href="{{ route(
                                        'student.tuitions.applications.index',
                                        $tuition
                                    ) }}"
                                    class="mt-5 flex w-full justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                                >
                                    View Applications
                                </a>

                                @if($tuition->status === 'filled')

                                    <div class="mt-3 rounded-xl bg-emerald-50 px-3 py-2 text-center text-sm font-semibold text-emerald-700">
                                        Teacher Selected
                                    </div>

                                @elseif($tuition->status === 'pending')

                                    <div class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-center text-sm font-semibold text-amber-700">
                                        Waiting for Admin Review
                                    </div>

                                @elseif($tuition->status === 'closed')

                                    <div class="mt-3 rounded-xl bg-slate-200 px-3 py-2 text-center text-sm font-semibold text-slate-600">
                                        Tuition Closed
                                    </div>

                                @endif

                            </div>

                        </aside>

                    </div>

                </div>

                <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-4 sm:px-6">

                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">

                        <div class="flex flex-col gap-1 text-xs text-slate-500 sm:flex-row sm:gap-5">

                            <span>
                                Posted:
                                {{
                                    $tuition->published_at
                                        ?->format('d M Y, h:i A')
                                    ?? $tuition->created_at
                                        ->format('d M Y, h:i A')
                                }}
                            </span>

                            <span>
                                Medium:
                                <strong class="text-slate-700">
                                    {{
                                        $tuition->medium
                                            ? ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $tuition->medium
                                                )
                                            )
                                            : 'Not specified'
                                    }}
                                </strong>
                            </span>

                        </div>

                        <div class="flex flex-wrap gap-2">

                            @if($canEdit)

                                <a
                                    href="{{ route(
                                        'student.tuitions.edit',
                                        $tuition
                                    ) }}"
                                    class="rounded-lg border border-indigo-200 bg-white px-4 py-2 text-xs font-bold text-indigo-700 transition hover:bg-indigo-50"
                                >
                                    Edit
                                </a>

                            @endif

                            @if($canClose)

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'student.tuitions.close',
                                        $tuition
                                    ) }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        onclick="return confirm('Close this tuition post? Teachers will no longer be able to apply.')"
                                        class="rounded-lg border border-amber-200 bg-white px-4 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-50"
                                    >
                                        Close Tuition
                                    </button>

                                </form>

                            @endif

                            @if($canDelete)

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'student.tuitions.destroy',
                                        $tuition
                                    ) }}"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        onclick="return confirm('Delete this tuition post permanently? This action cannot be undone.')"
                                        class="rounded-lg border border-rose-200 bg-white px-4 py-2 text-xs font-bold text-rose-600 transition hover:bg-rose-50"
                                    >
                                        Delete
                                    </button>

                                </form>

                            @else

                                <span
                                    title="A tuition with applications cannot be deleted."
                                    class="cursor-not-allowed rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-400"
                                >
                                    Delete Unavailable
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

                <h3 class="text-xl font-bold text-slate-900">
                    No tuition posts yet
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                    Create your first tuition requirement and start receiving applications from verified teachers.
                </p>

                <a
                    href="{{ route('student.tuitions.create') }}"
                    class="mt-6 inline-flex rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white"
                >
                    Post Your First Tuition
                </a>

            </div>

        @endforelse

    </div>

    @if($tuitions->hasPages())

        <div class="mt-8">
            {{ $tuitions->links() }}
        </div>

    @endif

@endsection