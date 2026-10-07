@extends('layouts.app')

@section('title', 'Teacher Profile Edit Requests - Tuition Hub')

@php
    $pageTitle = 'Teacher Profile Edit Requests';
    $pageSubtitle = 'Admin Review';
@endphp

@section('content')

<style>
    .th-modal {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(6px);

        opacity: 0;
        visibility: hidden;
        pointer-events: none;

        transition:
            opacity .22s ease,
            visibility .22s ease;
    }

    .th-modal:target {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .th-modal-card {
        width: 100%;
        max-width: 430px;
        border-radius: 24px;
        background: #ffffff;
        padding: 28px;
        box-shadow:
            0 25px 60px rgba(15, 23, 42, 0.28);

        opacity: 0;
        transform:
            translateY(24px)
            scale(.94);

        transition:
            opacity .25s ease,
            transform .25s ease;
    }

    .th-modal:target .th-modal-card {
        opacity: 1;
        transform:
            translateY(0)
            scale(1);
    }
</style>


<div class="mx-auto max-w-7xl">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <h2 class="text-2xl font-black text-slate-900">
                Teacher Profile Edit Requests
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Review requested changes before they are applied to live teacher profiles.
            </p>

        </div>

        <a
            href="{{ route('admin.teachers.index') }}"
            class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
        >
            Back to Teachers
        </a>

    </div>


    {{-- Filter --}}
    <form
        method="GET"
        action="{{ route('admin.teacher-profile-edit-requests.index') }}"
        class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >

        <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_220px_auto]">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Teacher name, email or phone"
                class="rounded-xl border border-slate-300 px-4 py-3 text-sm"
            >

            <select
                name="status"
                class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
            >

                <option value="">
                    All Statuses
                </option>

                <option
                    value="pending"
                    @selected(request('status') === 'pending')
                >
                    Pending
                </option>

                <option
                    value="approved"
                    @selected(request('status') === 'approved')
                >
                    Approved
                </option>

                <option
                    value="rejected"
                    @selected(request('status') === 'rejected')
                >
                    Rejected
                </option>

            </select>

            <div class="flex gap-2">

                <button
                    type="submit"
                    class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white"
                >
                    Filter
                </button>

                @if(
                    request()->filled('search') ||
                    request()->filled('status')
                )

                    <a
                        href="{{ route(
                            'admin.teacher-profile-edit-requests.index'
                        ) }}"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600"
                    >
                        Clear
                    </a>

                @endif

            </div>

        </div>

    </form>


    <div class="mt-6 space-y-6">

        @forelse($editRequests as $editRequest)

            @php
                $teacher =
                    $editRequest->teacherProfile;

                $data =
                    $editRequest->requested_data ?? [];

                $requestedSubjects =
                    collect(
                        $data['subject_ids'] ?? []
                    )
                        ->map(
                            fn ($id) =>
                                $subjectsById
                                    ->get((int) $id)
                                    ?->name
                        )
                        ->filter()
                        ->values();

                $requestedLocations =
                    collect(
                        $data['location_ids'] ?? []
                    )
                        ->map(function ($id) use ($locationsById) {

                            $location =
                                $locationsById->get(
                                    (int) $id
                                );

                            if (! $location) {
                                return null;
                            }

                            return
                                $location->area
                                .', '
                                .$location->district;
                        })
                        ->filter()
                        ->values();
            @endphp


            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                {{-- Header --}}
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">

                    <div>

                        <div class="flex flex-wrap items-center gap-2">

                            <h3 class="text-xl font-black text-slate-900">
                                {{
                                    $teacher?->user?->name
                                    ?? 'Teacher'
                                }}
                            </h3>

                            <span
                                class="rounded-full px-3 py-1 text-xs font-bold
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

                        <p class="mt-2 text-sm text-slate-500">

                            {{
                                $teacher?->user?->email
                                ?? 'No email'
                            }}

                            · Request #{{ $editRequest->id }}

                            · {{
                                $editRequest
                                    ->created_at
                                    ->format(
                                        'd M Y, h:i A'
                                    )
                            }}

                        </p>

                    </div>


                    @if($teacher)

                        <a
                            href="{{ route(
                                'admin.teachers.edit',
                                $teacher
                            ) }}"
                            class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800"
                        >
                            Edit Live Profile
                        </a>

                    @endif

                </div>


                {{-- Comparison Table --}}
                <div class="mt-6 overflow-x-auto">

                    <table class="min-w-full text-left text-sm">

                        <thead>

                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-400">

                                <th class="px-3 py-3">
                                    Field
                                </th>

                                <th class="px-3 py-3">
                                    Current
                                </th>

                                <th class="px-3 py-3">
                                    Requested
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-slate-100">

                            @foreach([
                                'Gender' => [
                                    $teacher?->gender
                                        ? ucfirst(
                                            $teacher->gender
                                        )
                                        : 'Not specified',

                                    isset($data['gender'])
                                        ? ucfirst(
                                            $data['gender']
                                        )
                                        : 'Not specified',
                                ],

                                'University' => [
                                    $teacher?->university
                                        ?? 'Not specified',

                                    $data['university']
                                        ?? 'Not specified',
                                ],

                                'Department' => [
                                    $teacher?->department
                                        ?? 'Not specified',

                                    $data['department']
                                        ?? 'Not specified',
                                ],

                                'Degree' => [
                                    $teacher?->degree
                                        ?? 'Not specified',

                                    $data['degree']
                                        ?? 'Not specified',
                                ],

                                'Experience' => [
                                    (
                                        $teacher
                                            ?->experience_years
                                        ?? 0
                                    )
                                    .' year(s)',

                                    (
                                        $data[
                                            'experience_years'
                                        ] ?? 0
                                    )
                                    .' year(s)',
                                ],

                                'Teaching Mode' => [
                                    ucfirst(
                                        $teacher
                                            ?->teaching_mode
                                        ?? 'offline'
                                    ),

                                    ucfirst(
                                        $data[
                                            'teaching_mode'
                                        ] ?? 'offline'
                                    ),
                                ],

                                'Availability' => [
                                    $teacher?->is_available
                                        ? 'Available'
                                        : 'Unavailable',

                                    ! empty(
                                        $data['is_available']
                                    )
                                        ? 'Available'
                                        : 'Unavailable',
                                ],
                            ] as $field => $values)

                                <tr>

                                    <td class="px-3 py-3 font-semibold text-slate-700">
                                        {{ $field }}
                                    </td>

                                    <td class="px-3 py-3 text-slate-600">
                                        {{ $values[0] }}
                                    </td>

                                    <td class="px-3 py-3 font-semibold text-indigo-700">
                                        {{ $values[1] }}
                                    </td>

                                </tr>

                            @endforeach


                            {{-- Salary --}}
                            <tr>

                                <td class="px-3 py-3 font-semibold text-slate-700">
                                    Expected Salary
                                </td>

                                <td class="px-3 py-3 text-slate-600">

                                    @if(
                                        $teacher?->expected_salary_min !== null ||
                                        $teacher?->expected_salary_max !== null
                                    )

                                        ৳{{
                                            number_format(
                                                (float) (
                                                    $teacher
                                                        ?->expected_salary_min
                                                    ?? 0
                                                )
                                            )
                                        }}

                                        -

                                        ৳{{
                                            number_format(
                                                (float) (
                                                    $teacher
                                                        ?->expected_salary_max
                                                    ?? 0
                                                )
                                            )
                                        }}

                                    @else

                                        Not specified

                                    @endif

                                </td>

                                <td class="px-3 py-3 font-semibold text-indigo-700">

                                    @if(
                                        (
                                            $data[
                                                'expected_salary_min'
                                            ] ?? null
                                        ) !== null
                                        ||
                                        (
                                            $data[
                                                'expected_salary_max'
                                            ] ?? null
                                        ) !== null
                                    )

                                        ৳{{
                                            number_format(
                                                (float) (
                                                    $data[
                                                        'expected_salary_min'
                                                    ] ?? 0
                                                )
                                            )
                                        }}

                                        -

                                        ৳{{
                                            number_format(
                                                (float) (
                                                    $data[
                                                        'expected_salary_max'
                                                    ] ?? 0
                                                )
                                            )
                                        }}

                                    @else

                                        Not specified

                                    @endif

                                </td>

                            </tr>


                            {{-- Bio --}}
                            <tr>

                                <td class="px-3 py-3 font-semibold text-slate-700">
                                    Bio
                                </td>

                                <td class="max-w-sm whitespace-pre-line px-3 py-3 text-slate-600">
                                    {{
                                        $teacher?->bio
                                        ?: 'Not specified'
                                    }}
                                </td>

                                <td class="max-w-sm whitespace-pre-line px-3 py-3 font-semibold text-indigo-700">
                                    {{
                                        $data['bio']
                                        ?? 'Not specified'
                                    }}
                                </td>

                            </tr>


                            {{-- Subjects --}}
                            <tr>

                                <td class="px-3 py-3 font-semibold text-slate-700">
                                    Subjects
                                </td>

                                <td class="px-3 py-3 text-slate-600">

                                    {{
                                        $teacher
                                            ?->subjects
                                            ?->pluck('name')
                                            ->join(', ')
                                        ?: 'None'
                                    }}

                                </td>

                                <td class="px-3 py-3 font-semibold text-indigo-700">

                                    {{
                                        $requestedSubjects
                                            ->join(', ')
                                        ?: 'None'
                                    }}

                                </td>

                            </tr>


                            {{-- Locations --}}
                            <tr>

                                <td class="px-3 py-3 font-semibold text-slate-700">
                                    Locations
                                </td>

                                <td class="px-3 py-3 text-slate-600">

                                    {{
                                        $teacher
                                            ?->locations
                                            ?->map(
                                                fn ($location) =>
                                                    $location->area
                                                    .', '
                                                    .$location->district
                                            )
                                            ->join(', ')
                                        ?: 'None'
                                    }}

                                </td>

                                <td class="px-3 py-3 font-semibold text-indigo-700">

                                    {{
                                        $requestedLocations
                                            ->join(', ')
                                        ?: 'None'
                                    }}

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                {{-- Requested Photo --}}
                @if(
                    $editRequest->profile_photo_path &&
                    $editRequest->status !== 'rejected'
                )

                    <div class="mt-6">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Requested Profile Photo
                        </p>

                        <div class="mt-3 flex flex-wrap gap-6">

                            @if($teacher?->profile_photo)

                                <div>

                                    <p class="mb-2 text-xs font-semibold text-slate-500">
                                        Current
                                    </p>

                                    <img
                                        src="{{ asset(
                                            'storage/'.
                                            $teacher->profile_photo
                                        ) }}"
                                        alt="Current"
                                        class="h-28 w-28 rounded-2xl border border-slate-200 object-cover"
                                    >

                                </div>

                            @endif


                            <div>

                                <p class="mb-2 text-xs font-semibold text-indigo-600">
                                    Requested
                                </p>

                                <img
                                    src="{{ asset(
                                        'storage/'.
                                        $editRequest
                                            ->profile_photo_path
                                    ) }}"
                                    alt="Requested"
                                    class="h-28 w-28 rounded-2xl border border-indigo-200 object-cover"
                                >

                            </div>

                        </div>

                    </div>

                @endif


                @if($editRequest->status === 'pending')

                    <div class="mt-7 grid gap-4 lg:grid-cols-2">

                        {{-- APPROVE OPEN BUTTON --}}
                        <a
                            href="#approve-request-{{ $editRequest->id }}"
                            class="block w-full rounded-xl bg-emerald-600 px-5 py-3 text-center text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-md"
                        >
                            Approve & Apply Changes
                        </a>


                        {{-- REJECT OPEN BUTTON --}}
                        <a
                            href="#reject-request-{{ $editRequest->id }}"
                            class="block w-full rounded-xl border border-rose-200 bg-white px-5 py-3 text-center text-sm font-bold text-rose-600 transition hover:-translate-y-0.5 hover:bg-rose-50"
                        >
                            Reject Request
                        </a>

                    </div>


                    {{-- =====================================================
                         APPROVE MODAL
                    ====================================================== --}}

                    <div
                        id="approve-request-{{ $editRequest->id }}"
                        class="th-modal"
                    >

                        {{-- Clickable Backdrop --}}
                        <a
                            href="#"
                            class="absolute inset-0"
                            aria-label="Close modal"
                        ></a>


                        <div class="th-modal-card relative z-10">

                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    class="h-7 w-7"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5 12.5l4 4L19 7"
                                    />
                                </svg>

                            </div>


                            <h3 class="mt-5 text-xl font-black text-slate-900">
                                Approve Profile Changes?
                            </h3>


                            <p class="mt-2 text-sm leading-6 text-slate-500">

                                The requested profile changes for

                                <strong class="text-slate-900">
                                    {{
                                        $teacher?->user?->name
                                        ?? 'this teacher'
                                    }}
                                </strong>

                                will be applied immediately.

                            </p>


                            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                                The approved information will replace the current live profile information.
                            </div>


                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.teacher-profile-edit-requests.approve',
                                    $editRequest
                                ) }}"
                                class="mt-7"
                            >

                                @csrf
                                @method('PATCH')


                                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                                    <a
                                        href="#"
                                        class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                    >
                                        Cancel
                                    </a>


                                    <button
                                        type="submit"
                                        class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700"
                                    >
                                        Approve Changes
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>


                    {{-- =====================================================
                         REJECT MODAL
                    ====================================================== --}}

                    <div
                        id="reject-request-{{ $editRequest->id }}"
                        class="th-modal"
                    >

                        <a
                            href="#"
                            class="absolute inset-0"
                            aria-label="Close modal"
                        ></a>


                        <div class="th-modal-card relative z-10">

                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    class="h-7 w-7"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6 6l12 12M18 6L6 18"
                                    />
                                </svg>

                            </div>


                            <h3 class="mt-5 text-xl font-black text-slate-900">
                                Reject Edit Request?
                            </h3>


                            <p class="mt-2 text-sm leading-6 text-slate-500">

                                The requested changes for

                                <strong class="text-slate-900">
                                    {{
                                        $teacher?->user?->name
                                        ?? 'this teacher'
                                    }}
                                </strong>

                                will not be applied.

                            </p>


                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.teacher-profile-edit-requests.reject',
                                    $editRequest
                                ) }}"
                                class="mt-6"
                            >

                                @csrf
                                @method('PATCH')


                                <label
                                    for="admin-note-{{ $editRequest->id }}"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Admin Note
                                </label>


                                <textarea
                                    id="admin-note-{{ $editRequest->id }}"
                                    name="admin_note"
                                    rows="4"
                                    maxlength="3000"
                                    placeholder="Optional reason for rejection..."
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-rose-400 focus:ring-4 focus:ring-rose-100"
                                ></textarea>


                                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                                    <a
                                        href="#"
                                        class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                    >
                                        Cancel
                                    </a>


                                    <button
                                        type="submit"
                                        class="rounded-xl bg-rose-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-rose-700"
                                    >
                                        Reject Request
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>


                @else

                    <div class="mt-6 rounded-xl bg-slate-50 p-4">

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                            <div>

                                <p class="text-sm font-semibold text-slate-700">
                                    Reviewed
                                </p>

                                @if($editRequest->reviewed_at)

                                    <p class="mt-1 text-xs text-slate-500">

                                        {{
                                            $editRequest
                                                ->reviewed_at
                                                ->format(
                                                    'd M Y, h:i A'
                                                )
                                        }}

                                        @if($editRequest->reviewedBy)

                                            by
                                            {{
                                                $editRequest
                                                    ->reviewedBy
                                                    ->name
                                            }}

                                        @endif

                                    </p>

                                @endif

                            </div>


                            <span
                                class="w-fit rounded-full px-3 py-1 text-xs font-bold
                                {{
                                    $editRequest->status === 'approved'
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-rose-50 text-rose-700'
                                }}"
                            >
                                {{
                                    strtoupper(
                                        $editRequest->status
                                    )
                                }}
                            </span>

                        </div>


                        @if($editRequest->admin_note)

                            <div class="mt-4 border-t border-slate-200 pt-4 text-sm text-slate-600">

                                <strong>
                                    Admin note:
                                </strong>

                                {{ $editRequest->admin_note }}

                            </div>

                        @endif

                    </div>

                @endif

            </article>


        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

                <h3 class="text-lg font-bold text-slate-800">
                    No profile edit requests
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    No teacher profile edit requests match the selected filters.
                </p>

            </div>

        @endforelse

    </div>


    @if($editRequests->hasPages())

        <div class="mt-8">
            {{ $editRequests->links() }}
        </div>

    @endif

</div>

@endsection