@extends('layouts.app')

@section('title', 'Teacher Verification - Tuition Hub Admin')

@php
    $pageTitle = 'Teacher Verification';
    $pageSubtitle = 'Admin Panel';
@endphp

@section('content')

<div class="mb-5 flex justify-end">

    <a
        href="{{ route(
            'admin.teacher-profile-edit-requests.index',
            [
                'status' => 'pending',
            ]
        ) }}"
        class="rounded-xl bg-violet-600 px-5 py-3 text-sm font-bold text-white hover:bg-violet-700"
    >
        Profile Edit Requests
    </a>

</div>

<div>

    <h2 class="text-2xl font-black text-slate-900">
        Teacher Verification
    </h2>

    <p class="mt-1 text-sm text-slate-500">
        Review, edit and verify teacher profiles.
    </p>

</div>

<form
    method="GET"
    action="{{ route('admin.teachers.index') }}"
    class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
>

    <div class="grid gap-4 md:grid-cols-3">

        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search name, email or phone..."
            class="rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
        >

        <select
            name="status"
            class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
        >

            <option value="">
                All Verification Status
            </option>

            <option
                value="pending"
                @selected(
                    request('status') === 'pending'
                )
            >
                Pending
            </option>

            <option
                value="verified"
                @selected(
                    request('status') === 'verified'
                )
            >
                Verified
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
                request()->filled('status')
            )

                <a
                    href="{{ route(
                        'admin.teachers.index'
                    ) }}"
                    class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600"
                >
                    Clear
                </a>

            @endif

        </div>

    </div>

</form>

<div class="mt-6 space-y-5">

    @forelse($teachers as $teacher)

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

            <div class="flex flex-col gap-6 lg:flex-row lg:justify-between">

                <div class="min-w-0 flex-1">

                    <div class="flex flex-wrap items-center gap-2">

                        <h3 class="text-xl font-bold text-slate-900">
                            {{
                                $teacher->user?->name
                                ?? 'Teacher'
                            }}
                        </h3>

                        @if($teacher->is_verified)

                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold text-emerald-700">
                                VERIFIED
                            </span>

                        @else

                            <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-bold text-amber-700">
                                PENDING
                            </span>

                        @endif

                        @if(
                            (
                                $teacher
                                    ->pending_profile_edit_requests_count
                                ?? 0
                            ) > 0
                        )

                            <span class="rounded-full bg-violet-50 px-3 py-1 text-[10px] font-bold text-violet-700">
                                PROFILE EDIT REQUEST
                            </span>

                        @endif

                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                        <div>

                            <p class="text-xs text-slate-400">
                                Email
                            </p>

                            <p class="mt-1 break-all text-sm font-semibold text-slate-800">
                                {{
                                    $teacher->user?->email
                                    ?? 'N/A'
                                }}
                            </p>

                        </div>

                        <div>

                            <p class="text-xs text-slate-400">
                                Phone
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{
                                    $teacher->user?->phone
                                    ?? 'N/A'
                                }}
                            </p>

                        </div>

                        <div>

                            <p class="text-xs text-slate-400">
                                Gender
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{
                                    $teacher->gender
                                        ? ucfirst(
                                            $teacher->gender
                                        )
                                        : 'Not specified'
                                }}
                            </p>

                        </div>

                        <div>

                            <p class="text-xs text-slate-400">
                                University
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{
                                    $teacher->university
                                    ?? 'Not specified'
                                }}
                            </p>

                        </div>

                        <div>

                            <p class="text-xs text-slate-400">
                                Department
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{
                                    $teacher->department
                                    ?? 'Not specified'
                                }}
                            </p>

                        </div>

                        <div>

                            <p class="text-xs text-slate-400">
                                Experience
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{
                                    $teacher
                                        ->experience_years
                                }}
                                year(s)
                            </p>

                        </div>

                    </div>

                    <div class="mt-5">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Subjects
                        </p>

                        <div class="mt-2 flex flex-wrap gap-2">

                            @forelse(
                                $teacher->subjects
                                as $subject
                            )

                                <span class="rounded-lg bg-indigo-50 px-3 py-1 text-sm text-indigo-700">
                                    {{
                                        $subject->name
                                    }}
                                </span>

                            @empty

                                <span class="text-sm text-slate-500">
                                    No subjects added.
                                </span>

                            @endforelse

                        </div>

                    </div>

                    <div class="mt-5">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Preferred Areas
                        </p>

                        <div class="mt-2 flex flex-wrap gap-2">

                            @forelse(
                                $teacher->locations
                                as $location
                            )

                                <span class="rounded-lg bg-slate-100 px-3 py-1 text-sm text-slate-700">

                                    {{
                                        $location->area ===
                                        $location->district
                                            ? $location->area.
                                                ' (Whole District)'
                                            : $location->area
                                    }}

                                    ·

                                    {{
                                        $location->district
                                    }}

                                </span>

                            @empty

                                <span class="text-sm text-slate-500">
                                    No areas added.
                                </span>

                            @endforelse

                        </div>

                    </div>

                </div>

                <div class="lg:w-52">

                    <a
                        href="{{ route(
                            'admin.teachers.edit',
                            $teacher
                        ) }}"
                        class="mb-3 block w-full rounded-xl bg-slate-900 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-indigo-600"
                    >
                        Edit Profile
                    </a>

                    @if(
                        (
                            $teacher
                                ->pending_profile_edit_requests_count
                            ?? 0
                        ) > 0
                    )

                        <a
                            href="{{ route(
                                'admin.teacher-profile-edit-requests.index',
                                [
                                    'status' =>
                                        'pending',

                                    'search' =>
                                        $teacher
                                            ->user
                                            ?->email,
                                ]
                            ) }}"
                            class="mb-3 block w-full rounded-xl bg-violet-50 px-4 py-3 text-center text-sm font-bold text-violet-700 transition hover:bg-violet-100"
                        >
                            Review Edit Request
                        </a>

                    @endif

                    @if($teacher->is_verified)

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.teachers.unverify',
                                $teacher
                            ) }}"
                        >

                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                onclick="return confirm(
                                    'Remove verification from this teacher?'
                                )"
                                class="w-full rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                            >
                                Remove Verification
                            </button>

                        </form>

                    @else

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.teachers.verify',
                                $teacher
                            ) }}"
                        >

                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                onclick="return confirm(
                                    'Verify this teacher?'
                                )"
                                class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700"
                            >
                                Verify Teacher
                            </button>

                        </form>

                    @endif

                    @if(
                        Route::has(
                            'admin.verification-documents.index'
                        )
                    )

                        <a
                            href="{{ route(
                                'admin.verification-documents.index',
                                [
                                    'search' =>
                                        $teacher
                                            ->user
                                            ?->email,
                                ]
                            ) }}"
                            class="mt-3 block rounded-xl bg-indigo-50 px-4 py-3 text-center text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100"
                        >
                            View Documents
                        </a>

                    @endif

                </div>

            </div>

        </article>

    @empty

        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

            <h3 class="text-lg font-bold text-slate-800">
                No teachers found
            </h3>

            <p class="mt-2 text-sm text-slate-500">
                No teacher profiles match the selected filters.
            </p>

        </div>

    @endforelse

</div>

@if($teachers->hasPages())

    <div class="mt-8">
        {{ $teachers->links() }}
    </div>

@endif

@endsection