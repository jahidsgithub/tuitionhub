@extends('layouts.app')

@section('title', 'My Assignments - Tuition Hub')

@php
    $pageTitle = 'My Assignments';
    $pageSubtitle = 'Student Marketplace';
@endphp

@section('content')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

        <div>

            <h2 class="text-2xl font-black text-slate-900">
                My Tuition Assignments
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                View assigned teachers, contact details, reviews and complaints.
            </p>

        </div>

        <a
            href="{{ route('student.complaints.index') }}"
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

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="p-5 sm:p-6">

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
                                        Mode
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
                                        Assigned Via
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-800">
                                        {{
                                            $assignment->source === 'application'
                                                ? 'Application'
                                                : 'Direct Request'
                                        }}
                                    </p>

                                </div>

                            </div>

                            <div class="mt-6 border-t border-slate-100 pt-5">

                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Assigned Teacher
                                </p>

                                <h4 class="mt-2 text-lg font-bold text-slate-900">
                                    {{
                                        $assignment
                                            ->teacherProfile
                                            ?->user
                                            ?->name
                                        ?? 'N/A'
                                    }}
                                </h4>

                                @if($assignment->teacherProfile?->university)

                                    <p class="mt-1 text-sm text-slate-600">
                                        {{ $assignment->teacherProfile->university }}
                                    </p>

                                @endif

                                @if($assignment->teacherProfile?->department)

                                    <p class="text-sm text-slate-500">
                                        {{ $assignment->teacherProfile->department }}
                                    </p>

                                @endif

                            </div>

                            @if($assignment->status === 'active')

                                <div class="mt-5 rounded-2xl border border-indigo-100 bg-indigo-50 p-5">

                                    <h4 class="font-bold text-indigo-900">
                                        Teacher Contact Unlocked
                                    </h4>

                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">

                                        <div>

                                            <p class="text-xs text-indigo-500">
                                                Email
                                            </p>

                                            <p class="mt-1 break-all text-sm font-semibold text-indigo-950">
                                                {{
                                                    $assignment
                                                        ->teacherProfile
                                                        ?->user
                                                        ?->email
                                                    ?? 'N/A'
                                                }}
                                            </p>

                                        </div>

                                        <div>

                                            <p class="text-xs text-indigo-500">
                                                Phone
                                            </p>

                                            <p class="mt-1 text-sm font-semibold text-indigo-950">
                                                {{
                                                    $assignment
                                                        ->teacherProfile
                                                        ?->user
                                                        ?->phone
                                                    ?? 'N/A'
                                                }}
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            @else

                                <div class="mt-5 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">
                                    Contact information is available only while the assignment is active.
                                </div>

                            @endif

                            @if($assignment->status === 'completed')

                                @if($assignment->review)

                                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5">

                                        <h4 class="font-bold text-slate-900">
                                            Your Review
                                        </h4>

                                        <div class="mt-2 text-xl">

                                            <span class="text-amber-500">
                                                {{ str_repeat(
                                                    '★',
                                                    $assignment->review->rating
                                                ) }}
                                            </span>

                                            <span class="text-slate-300">
                                                {{ str_repeat(
                                                    '★',
                                                    5 - $assignment->review->rating
                                                ) }}
                                            </span>

                                        </div>

                                        @if($assignment->review->review)

                                            <p class="mt-3 text-sm leading-6 text-slate-700">
                                                {{ $assignment->review->review }}
                                            </p>

                                        @endif

                                    </div>

                                @else

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'student.assignments.review.store',
                                            $assignment
                                        ) }}"
                                        class="mt-5 rounded-2xl border border-slate-200 p-5"
                                    >
                                        @csrf

                                        <h4 class="text-lg font-bold text-slate-900">
                                            Rate Your Teacher
                                        </h4>

                                        <p class="mt-1 text-sm text-slate-500">
                                            Share your experience after completing this tuition.
                                        </p>

                                        <div class="mt-4">

                                            <label
                                                for="rating-{{ $assignment->id }}"
                                                class="mb-2 block text-sm font-semibold text-slate-700"
                                            >
                                                Rating
                                            </label>

                                            <select
                                                id="rating-{{ $assignment->id }}"
                                                name="rating"
                                                required
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                            >
                                                <option value="">
                                                    Select Rating
                                                </option>

                                                <option value="5">
                                                    5 - Excellent
                                                </option>

                                                <option value="4">
                                                    4 - Very Good
                                                </option>

                                                <option value="3">
                                                    3 - Good
                                                </option>

                                                <option value="2">
                                                    2 - Fair
                                                </option>

                                                <option value="1">
                                                    1 - Poor
                                                </option>
                                            </select>

                                        </div>

                                        <div class="mt-4">

                                            <label
                                                for="review-{{ $assignment->id }}"
                                                class="mb-2 block text-sm font-semibold text-slate-700"
                                            >
                                                Review
                                            </label>

                                            <textarea
                                                id="review-{{ $assignment->id }}"
                                                name="review"
                                                rows="4"
                                                maxlength="2000"
                                                placeholder="Share your experience..."
                                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                            >{{ old('review') }}</textarea>

                                        </div>

                                        <button
                                            type="submit"
                                            class="mt-4 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                                        >
                                            Submit Review
                                        </button>

                                    </form>

                                @endif

                            @endif

                        </div>

                        <div class="space-y-3 lg:w-60">

                            @if($assignment->status === 'active')

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'student.assignments.complete',
                                        $assignment
                                    ) }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        onclick="return confirm('Mark this tuition assignment as completed?')"
                                        class="w-full rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700"
                                    >
                                        Mark Completed
                                    </button>

                                </form>

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'student.assignments.cancel',
                                        $assignment
                                    ) }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        onclick="return confirm('Cancel this final tuition assignment?')"
                                        class="w-full rounded-xl bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"
                                    >
                                        Cancel Assignment
                                    </button>

                                </form>

                            @endif

                            <a
                                href="{{ route(
                                    'student.complaints.create',
                                    $assignment
                                ) }}"
                                class="block w-full rounded-xl border border-rose-200 bg-white px-5 py-3 text-center text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                            >
                                Report / Complaint
                            </a>

                            <a
                                href="{{ route('student.complaints.index') }}"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-5 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                            >
                                My Complaints
                            </a>

                        </div>

                    </div>

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

                <h3 class="text-xl font-bold text-slate-800">
                    No final assignments yet
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Assignments appear here after you select a teacher.
                </p>

            </div>

        @endforelse

    </div>

    @if($assignments->hasPages())

        <div class="mt-8">
            {{ $assignments->links() }}
        </div>

    @endif

@endsection