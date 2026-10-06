@extends('layouts.app')

@section('title', 'My Assignments - Tuition Hub')

@php
    $pageTitle = 'My Assignments';
    $pageSubtitle = 'Teacher Marketplace';
@endphp

@section('content')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

        <div>

            <h2 class="text-2xl font-black text-slate-900">
                My Tuition Assignments
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Final tuition jobs that have been assigned to you.
            </p>

        </div>

        <a
            href="{{ route('teacher.complaints.index') }}"
            class="inline-flex justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
        >
            View My Complaints
        </a>

    </div>

    <div class="mt-6 space-y-5">

        @forelse($assignments as $assignment)

            @php
                $statusClasses = match($assignment->status) {
                    'active' => 'bg-emerald-50 text-emerald-700',
                    'completed' => 'bg-indigo-50 text-indigo-700',
                    default => 'bg-rose-50 text-rose-700',
                };
            @endphp

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <div class="flex flex-col gap-6 lg:flex-row lg:justify-between">

                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-2">

                            <h3 class="text-xl font-bold text-slate-900">
                                {{ $assignment->tuitionPost?->title ?? 'Unknown Tuition' }}
                            </h3>

                            <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $statusClasses }}">
                                {{ strtoupper($assignment->status) }}
                            </span>

                        </div>

                        <p class="mt-2 text-sm text-slate-500">
                            Tuition Code:
                            <strong class="text-slate-700">
                                {{ $assignment->tuitionPost?->tuition_code ?? 'N/A' }}
                            </strong>
                        </p>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">

                            <div class="rounded-xl bg-slate-50 p-4">

                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Class
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{ $assignment->tuitionPost?->class_level ?? 'N/A' }}
                                </p>

                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">

                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Salary
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">

                                    @if($assignment->tuitionPost?->salary !== null)

                                        ৳{{ number_format(
                                            (float) $assignment->tuitionPost->salary
                                        ) }}

                                    @else

                                        N/A

                                    @endif

                                </p>

                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">

                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Teaching Mode
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{
                                        ucfirst(
                                            $assignment->tuitionPost?->teaching_mode
                                            ?? 'N/A'
                                        )
                                    }}
                                </p>

                            </div>

                            <div class="rounded-xl bg-slate-50 p-4">

                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Assigned
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{
                                        $assignment
                                            ->assigned_at
                                            ?->format('d M Y')
                                        ?? 'N/A'
                                    }}
                                </p>

                            </div>

                        </div>

                        <div class="mt-6 border-t border-slate-100 pt-5">

                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Student / Guardian
                            </p>

                            <h4 class="mt-2 text-lg font-bold text-slate-900">
                                {{ $assignment->student?->name ?? 'N/A' }}
                            </h4>

                        </div>

                        @if($assignment->status === 'active')

                            <div class="mt-5 rounded-2xl border border-indigo-100 bg-indigo-50 p-5">

                                <h4 class="font-bold text-indigo-900">
                                    Student / Guardian Contact Unlocked
                                </h4>

                                <div class="mt-4 grid gap-4 sm:grid-cols-2">

                                    <div>

                                        <p class="text-xs text-indigo-500">
                                            Name
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-indigo-950">
                                            {{ $assignment->student?->name ?? 'N/A' }}
                                        </p>

                                    </div>

                                    <div>

                                        <p class="text-xs text-indigo-500">
                                            Phone
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-indigo-950">
                                            {{ $assignment->student?->phone ?? 'N/A' }}
                                        </p>

                                    </div>

                                    <div>

                                        <p class="text-xs text-indigo-500">
                                            Email
                                        </p>

                                        <p class="mt-1 break-all text-sm font-semibold text-indigo-950">
                                            {{ $assignment->student?->email ?? 'N/A' }}
                                        </p>

                                    </div>

                                    @if(
                                        $assignment
                                            ->student
                                            ?->studentProfile
                                            ?->guardian_name
                                    )

                                        <div>

                                            <p class="text-xs text-indigo-500">
                                                Guardian
                                            </p>

                                            <p class="mt-1 text-sm font-semibold text-indigo-950">
                                                {{
                                                    $assignment
                                                        ->student
                                                        ->studentProfile
                                                        ->guardian_name
                                                }}
                                            </p>

                                        </div>

                                    @endif

                                    @if(
                                        $assignment
                                            ->student
                                            ?->studentProfile
                                            ?->guardian_phone
                                    )

                                        <div>

                                            <p class="text-xs text-indigo-500">
                                                Guardian Phone
                                            </p>

                                            <p class="mt-1 text-sm font-semibold text-indigo-950">
                                                {{
                                                    $assignment
                                                        ->student
                                                        ->studentProfile
                                                        ->guardian_phone
                                                }}
                                            </p>

                                        </div>

                                    @endif

                                    @if(
                                        $assignment
                                            ->student
                                            ?->studentProfile
                                            ?->address
                                    )

                                        <div class="sm:col-span-2">

                                            <p class="text-xs text-indigo-500">
                                                Address
                                            </p>

                                            <p class="mt-1 text-sm font-semibold leading-6 text-indigo-950">
                                                {{
                                                    $assignment
                                                        ->student
                                                        ->studentProfile
                                                        ->address
                                                }}
                                            </p>

                                        </div>

                                    @endif

                                </div>

                            </div>

                        @else

                            <div class="mt-5 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">
                                Contact information is available only while the assignment is active.
                            </div>

                        @endif

                    </div>

                    <div class="space-y-3 lg:w-60">

                        <a
                            href="{{ route(
                                'teacher.complaints.create',
                                $assignment
                            ) }}"
                            class="block w-full rounded-xl border border-rose-200 bg-white px-5 py-3 text-center text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                        >
                            Report / Complaint
                        </a>

                        <a
                            href="{{ route('teacher.complaints.index') }}"
                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-5 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                        >
                            My Complaints
                        </a>

                    </div>

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

                <h3 class="text-xl font-bold text-slate-900">
                    No final assignments yet
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Jobs will appear here after a student finally selects you.
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

    @if($assignments->hasPages())

        <div class="mt-8">
            {{ $assignments->links() }}
        </div>

    @endif

@endsection