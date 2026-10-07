@extends('layouts.app')

@section('title', 'Teacher Profile - Tuition Hub')

@php
    $pageTitle = 'Teacher Profile';
    $pageSubtitle = 'Teacher Account';

    $pendingEditRequest =
        $profile->pendingProfileEditRequest;
@endphp

@section('content')

<div class="mx-auto max-w-5xl">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div>

            <h2 class="text-2xl font-black text-slate-900">
                Teacher Profile
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Your teacher profile is managed by the Tuition Hub administration.
            </p>

        </div>

        <div class="flex flex-wrap gap-2">

            @if($profile->is_verified)

                <span class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700">
                    Verified Teacher
                </span>

            @else

                <span class="rounded-full bg-amber-50 px-4 py-2 text-sm font-bold text-amber-700">
                    Verification Pending
                </span>

            @endif

            @if($profile->is_available)

                <span class="rounded-full bg-indigo-50 px-4 py-2 text-sm font-bold text-indigo-700">
                    Available
                </span>

            @else

                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-bold text-slate-600">
                    Unavailable
                </span>

            @endif

        </div>

    </div>

    @if($pendingEditRequest)

        <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="font-bold text-amber-900">
                        Profile edit request pending
                    </p>

                    <p class="mt-1 text-sm leading-6 text-amber-700">
                        Your requested changes are waiting for admin review.
                        Your current profile remains unchanged until approval.
                    </p>

                </div>

                <span class="w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                    PENDING
                </span>

            </div>

        </div>

    @else

        <div class="mt-5 rounded-2xl border border-indigo-100 bg-indigo-50 p-5">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="font-bold text-indigo-900">
                        Need to change your profile?
                    </p>

                    <p class="mt-1 text-sm leading-6 text-indigo-700">
                        Teachers cannot directly edit profile information.
                        Submit a change request and an admin will review it.
                    </p>

                </div>

                <a
                    href="{{ route('teacher.profile.request.edit') }}"
                    class="inline-flex shrink-0 justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Request Profile Edit
                </a>

            </div>

        </div>

    @endif

    <div class="mt-6 grid gap-5 lg:grid-cols-[280px_minmax(0,1fr)]">

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex flex-col items-center text-center">

                @if($profile->profile_photo)

                    <img
                        src="{{ asset(
                            'storage/'.$profile->profile_photo
                        ) }}"
                        alt="{{ $user->name }}"
                        class="h-32 w-32 rounded-2xl border border-slate-200 object-cover"
                    >

                @else

                    <div class="flex h-32 w-32 items-center justify-center rounded-2xl bg-indigo-50 text-4xl font-black text-indigo-700">
                        {{
                            strtoupper(
                                substr(
                                    $user->name,
                                    0,
                                    1
                                )
                            )
                        }}
                    </div>

                @endif

                <h3 class="mt-4 text-xl font-black text-slate-900">
                    {{ $user->name }}
                </h3>

                <p class="mt-1 break-all text-sm text-slate-500">
                    {{ $user->email }}
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $user->phone ?? 'No phone added' }}
                </p>

            </div>

        </section>

        <div class="space-y-5">

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h3 class="text-lg font-bold text-slate-900">
                    Profile Information
                </h3>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Gender
                        </p>

                        <p class="mt-1 font-semibold text-slate-800">
                            {{
                                $profile->gender
                                    ? ucfirst($profile->gender)
                                    : 'Not specified'
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            University / Institution
                        </p>

                        <p class="mt-1 font-semibold text-slate-800">
                            {{
                                $profile->university
                                ?? 'Not specified'
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Department
                        </p>

                        <p class="mt-1 font-semibold text-slate-800">
                            {{
                                $profile->department
                                ?? 'Not specified'
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Degree
                        </p>

                        <p class="mt-1 font-semibold text-slate-800">
                            {{
                                $profile->degree
                                ?? 'Not specified'
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Experience
                        </p>

                        <p class="mt-1 font-semibold text-slate-800">
                            {{
                                $profile->experience_years
                                ?? 0
                            }}
                            year(s)
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Teaching Mode
                        </p>

                        <p class="mt-1 font-semibold text-slate-800">
                            {{
                                ucfirst(
                                    $profile->teaching_mode
                                    ?? 'offline'
                                )
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Expected Salary
                        </p>

                        <p class="mt-1 font-semibold text-slate-800">

                            @if(
                                $profile->expected_salary_min !== null ||
                                $profile->expected_salary_max !== null
                            )

                                ৳{{
                                    number_format(
                                        (float) (
                                            $profile
                                                ->expected_salary_min
                                            ?? 0
                                        )
                                    )
                                }}

                                -

                                ৳{{
                                    number_format(
                                        (float) (
                                            $profile
                                                ->expected_salary_max
                                            ?? 0
                                        )
                                    )
                                }}

                            @else

                                Not specified

                            @endif

                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Marketplace Status
                        </p>

                        <p class="mt-1 font-semibold text-slate-800">
                            {{
                                $profile->is_available
                                    ? 'Available'
                                    : 'Unavailable'
                            }}
                        </p>
                    </div>

                </div>

                <div class="mt-5 border-t border-slate-100 pt-5">

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        About
                    </p>

                    <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600">
                        {{
                            $profile->bio
                            ?: 'No bio added.'
                        }}
                    </p>

                </div>

            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h3 class="text-lg font-bold text-slate-900">
                    Subjects
                </h3>

                <div class="mt-4 flex flex-wrap gap-2">

                    @forelse($profile->subjects as $subject)

                        <span class="rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700">
                            {{ $subject->name }}
                        </span>

                    @empty

                        <span class="text-sm text-slate-500">
                            No subjects added yet.
                        </span>

                    @endforelse

                </div>

            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h3 class="text-lg font-bold text-slate-900">
                    Teaching Locations
                </h3>

                <div class="mt-4 flex flex-wrap gap-2">

                    @forelse($profile->locations as $location)

                        <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700">

                            {{
                                $location->area ===
                                $location->district
                                    ? $location->area.
                                        ' (Whole District)'
                                    : $location->area
                            }}

                            · {{ $location->district }}

                        </span>

                    @empty

                        <span class="text-sm text-slate-500">
                            No locations added yet.
                        </span>

                    @endforelse

                </div>

            </section>

        </div>

    </div>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

        <h3 class="text-lg font-bold text-slate-900">
            Recent Profile Edit Requests
        </h3>

        <div class="mt-4 divide-y divide-slate-100">

            @forelse($recentEditRequests as $editRequest)

                <div class="flex flex-col gap-2 py-4 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <p class="text-sm font-semibold text-slate-800">
                            Request #{{ $editRequest->id }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Submitted
                            {{
                                $editRequest
                                    ->created_at
                                    ->format(
                                        'd M Y, h:i A'
                                    )
                            }}
                        </p>

                        @if($editRequest->admin_note)

                            <p class="mt-2 text-sm text-slate-600">
                                Admin note:
                                {{ $editRequest->admin_note }}
                            </p>

                        @endif

                    </div>

                    <span
                        class="w-fit rounded-full px-3 py-1 text-xs font-bold
                        {{
                            $editRequest->status === 'approved'
                                ? 'bg-emerald-50 text-emerald-700'
                                : (
                                    $editRequest->status === 'rejected'
                                        ? 'bg-rose-50 text-rose-700'
                                        : 'bg-amber-50 text-amber-700'
                                )
                        }}"
                    >
                        {{
                            strtoupper(
                                $editRequest->status
                            )
                        }}
                    </span>

                </div>

            @empty

                <p class="py-4 text-sm text-slate-500">
                    No profile edit requests yet.
                </p>

            @endforelse

        </div>

    </section>

</div>

@endsection