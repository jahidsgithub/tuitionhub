@extends('layouts.app')

@section('title', 'Tuition Applications - Tuition Hub')

@php
    $pageTitle = 'Teacher Applications';
    $pageSubtitle = $tuition->tuition_code;
@endphp

@section('content')

    <div class="mb-5">

        <a
            href="{{ route('student.tuitions.index') }}"
            class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
        >
            ← Back to My Tuition Posts
        </a>

    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

        <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">

            <div>

                <div class="flex flex-wrap items-center gap-3">

                    <h2 class="text-2xl font-black text-slate-900">
                        {{ $tuition->title }}
                    </h2>

                    <span class="rounded-full px-3 py-1 text-xs font-bold
                        {{
                            $tuition->status === 'filled'
                                ? 'bg-emerald-50 text-emerald-700'
                                : 'bg-indigo-50 text-indigo-700'
                        }}"
                    >
                        {{ strtoupper($tuition->status) }}
                    </span>

                </div>

                <p class="mt-2 text-sm text-slate-500">
                    Tuition ID: {{ $tuition->tuition_code }}
                </p>

                <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-600">

                    <span>
                        <strong>Class:</strong>
                        {{ $tuition->class_level }}
                    </span>

                    <span>
                        <strong>Location:</strong>
                        {{ $tuition->location?->area ?? 'Not specified' }}
                    </span>

                    <span>
                        <strong>Salary:</strong>
                        ৳{{ number_format((float) $tuition->salary) }}/month
                    </span>

                </div>

                <p class="mt-3 text-sm text-slate-600">
                    <strong>Subjects:</strong>
                    {{ $tuition->subjects->pluck('name')->join(', ') }}
                </p>

            </div>

        </div>

    </section>

    <div class="mt-7">

        <h2 class="text-xl font-black text-slate-900">
            Teacher Applications
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Review applicants and select the most suitable teacher.
        </p>

    </div>

    <div class="mt-5 space-y-4">

        @forelse($applications as $application)

            @php
                $teacher = $application->teacherProfile;
                $teacherUser = $teacher?->user;

                $applicationStatusClasses = match($application->status) {
                    'accepted' => 'bg-emerald-50 text-emerald-700',
                    'rejected' => 'bg-rose-50 text-rose-700',
                    'shortlisted' => 'bg-indigo-50 text-indigo-700',
                    default => 'bg-amber-50 text-amber-700',
                };
            @endphp

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <div class="flex flex-col gap-6 lg:flex-row lg:justify-between">

                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-2">

                            <div class="mr-2 flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 font-black text-indigo-700">
                                {{
                                    strtoupper(
                                        substr(
                                            $teacherUser?->name ?? 'T',
                                            0,
                                            1
                                        )
                                    )
                                }}
                            </div>

                            <h3 class="text-xl font-bold text-slate-900">
                                {{ $teacherUser?->name ?? 'Teacher' }}
                            </h3>

                            @if($teacher?->is_verified)

                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">
                                    VERIFIED
                                </span>

                            @endif

                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $applicationStatusClasses }}">
                                {{ strtoupper($application->status) }}
                            </span>

                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">

                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    University
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $teacher?->university ?? 'Not specified' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Department
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $teacher?->department ?? 'Not specified' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Degree
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $teacher?->degree ?? 'Not specified' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Experience
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $teacher?->experience_years ?? 0 }} year(s)
                                </p>
                            </div>

                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Teaching Mode
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{
                                        ucfirst(
                                            $teacher?->teaching_mode
                                            ?? 'Not specified'
                                        )
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Expected Salary
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

                        <div class="mt-5">

                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Subjects
                            </p>

                            <div class="mt-2 flex flex-wrap gap-2">

                                @forelse($teacher?->subjects ?? [] as $subject)

                                    <span class="rounded-lg bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700">
                                        {{ $subject->name }}
                                    </span>

                                @empty

                                    <span class="text-sm text-slate-500">
                                        No subjects added.
                                    </span>

                                @endforelse

                            </div>

                        </div>

                        @if($application->message)

                            <div class="mt-5 rounded-2xl bg-slate-50 p-4">

                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Teacher's Message
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">
                                    {{ $application->message }}
                                </p>

                            </div>

                        @endif

                    </div>

                    <div class="lg:w-52">

                        @if(
                            $tuition->status !== 'filled' &&
                            $application->status !== 'accepted' &&
                            $application->status !== 'rejected'
                        )

                            @if($application->status !== 'shortlisted')

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'student.tuitions.applications.shortlist',
                                        [
                                            $tuition,
                                            $application,
                                        ]
                                    ) }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-indigo-50 px-4 py-2.5 text-sm font-bold text-indigo-700 transition hover:bg-indigo-100"
                                    >
                                        Shortlist
                                    </button>

                                </form>

                            @endif

                            <form
                                method="POST"
                                action="{{ route(
                                    'student.tuitions.applications.accept',
                                    [
                                        $tuition,
                                        $application,
                                    ]
                                ) }}"
                                class="mt-3"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Select this teacher for the tuition?')"
                                    class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700"
                                >
                                    Accept Teacher
                                </button>

                            </form>

                            <form
                                method="POST"
                                action="{{ route(
                                    'student.tuitions.applications.reject',
                                    [
                                        $tuition,
                                        $application,
                                    ]
                                ) }}"
                                class="mt-3"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Reject this application?')"
                                    class="w-full rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                                >
                                    Reject
                                </button>

                            </form>

                        @elseif($application->status === 'accepted')

                            <div class="rounded-xl bg-emerald-50 p-4 text-center text-sm font-bold text-emerald-700">
                                Selected Teacher
                            </div>

                        @else

                            <div class="rounded-xl bg-slate-100 p-4 text-center text-sm font-semibold text-slate-600">
                                {{ ucfirst($application->status) }}
                            </div>

                        @endif

                    </div>

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

                <h3 class="text-lg font-bold text-slate-800">
                    No applications yet
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Teachers have not applied for this tuition yet.
                </p>

            </div>

        @endforelse

    </div>

    @if($applications->hasPages())

        <div class="mt-8">
            {{ $applications->links() }}
        </div>

    @endif

@endsection