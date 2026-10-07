@extends('layouts.app')

@section('title', 'Edit Teacher Profile - Tuition Hub')

@php
    $pageTitle = 'Edit Teacher Profile';
    $pageSubtitle = 'Admin';

    $selectedSubjects = old(
        'subjects',
        $teacher->subjects->pluck('id')->toArray()
    );

    $selectedLocations = old(
        'locations',
        $teacher->locations->pluck('id')->toArray()
    );

    $subjectGroups =
        $subjects->groupBy('category');

    $categoryOrder = [
        'General',
        'Science',
        'Business Studies',
        'Humanities',
        'Other',
    ];
@endphp

@section('content')

<div class="mx-auto max-w-5xl">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <a
                href="{{ route('admin.teachers.index') }}"
                class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
            >
                ← Back to Teachers
            </a>

            <h2 class="mt-3 text-2xl font-black text-slate-900">
                Edit Teacher Profile
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Changes made here are applied directly to the teacher profile.
            </p>

        </div>

        <div class="flex flex-wrap gap-2">

            @if($teacher->is_verified)

                <span class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700">
                    Verified
                </span>

            @else

                <span class="rounded-full bg-amber-50 px-4 py-2 text-sm font-bold text-amber-700">
                    Verification Pending
                </span>

            @endif

        </div>

    </div>

    @if($teacher->pendingProfileEditRequest)

        <div class="mt-5 rounded-2xl border border-violet-200 bg-violet-50 p-5">

            <p class="font-bold text-violet-900">
                Teacher has a pending profile edit request
            </p>

            <p class="mt-1 text-sm text-violet-700">
                You can review the request separately or directly edit this profile as an admin.
            </p>

            <a
                href="{{ route(
                    'admin.teacher-profile-edit-requests.index',
                    [
                        'status' => 'pending',
                        'search' => $teacher->user?->email,
                    ]
                ) }}"
                class="mt-4 inline-flex rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-violet-700"
            >
                Review Pending Request
            </a>

        </div>

    @endif

    <form
        method="POST"
        action="{{ route(
            'admin.teachers.update',
            $teacher
        ) }}"
        enctype="multipart/form-data"
        class="mt-6 space-y-5"
    >
        @csrf
        @method('PUT')

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="text-lg font-bold text-slate-900">
                Profile Photo
            </h3>

            <div class="mt-5 grid gap-5 md:grid-cols-[180px_minmax(0,1fr)]">

                <div>

                    @if($teacher->profile_photo)

                        <img
                            src="{{ asset(
                                'storage/'.$teacher->profile_photo
                            ) }}"
                            alt="{{ $teacher->user?->name }}"
                            class="h-32 w-32 rounded-2xl border border-slate-200 object-cover"
                        >

                    @else

                        <div class="flex h-32 w-32 items-center justify-center rounded-2xl bg-indigo-50 text-4xl font-black text-indigo-700">
                            {{
                                strtoupper(
                                    substr(
                                        $teacher->user?->name ?? 'T',
                                        0,
                                        1
                                    )
                                )
                            }}
                        </div>

                    @endif

                </div>

                <div>

                    <label
                        for="profile_photo"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        New Profile Photo
                    </label>

                    <input
                        id="profile_photo"
                        type="file"
                        name="profile_photo"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"
                    >

                    <p class="mt-2 text-xs text-slate-500">
                        Leave empty to keep the current photo.
                        Maximum file size 2 MB.
                    </p>

                </div>

            </div>

        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="text-lg font-bold text-slate-900">
                Account Information
            </h3>

            <div class="mt-5 grid gap-5 md:grid-cols-2">

                <div>

                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Name
                    </label>

                    <input
                        type="text"
                        value="{{ $teacher->user?->name }}"
                        disabled
                        class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-500"
                    >

                </div>

                <div>

                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Email
                    </label>

                    <input
                        type="text"
                        value="{{ $teacher->user?->email }}"
                        disabled
                        class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-500"
                    >

                </div>

                <div>

                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Phone
                    </label>

                    <input
                        type="text"
                        value="{{ $teacher->user?->phone ?? 'Not added' }}"
                        disabled
                        class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-500"
                    >

                </div>

                <div>

                    <label
                        for="gender"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Gender
                    </label>

                    <select
                        id="gender"
                        name="gender"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
                    >

                        <option value="">
                            Select Gender
                        </option>

                        @foreach([
                            'male' => 'Male',
                            'female' => 'Female',
                            'other' => 'Other',
                        ] as $value => $label)

                            <option
                                value="{{ $value }}"
                                @selected(
                                    old(
                                        'gender',
                                        $teacher->gender
                                    ) === $value
                                )
                            >
                                {{ $label }}
                            </option>

                        @endforeach

                    </select>

                </div>

            </div>

        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="text-lg font-bold text-slate-900">
                Education & Experience
            </h3>

            <div class="mt-5 grid gap-5 md:grid-cols-2">

                <div>

                    <label
                        for="university"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        University / Institution
                    </label>

                    <input
                        id="university"
                        type="text"
                        name="university"
                        value="{{ old(
                            'university',
                            $teacher->university
                        ) }}"
                        required
                        maxlength="255"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                    >

                </div>

                <div>

                    <label
                        for="department"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Department
                    </label>

                    <input
                        id="department"
                        type="text"
                        name="department"
                        value="{{ old(
                            'department',
                            $teacher->department
                        ) }}"
                        required
                        maxlength="255"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                    >

                </div>

                <div>

                    <label
                        for="degree"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Degree
                    </label>

                    <input
                        id="degree"
                        type="text"
                        name="degree"
                        value="{{ old(
                            'degree',
                            $teacher->degree
                        ) }}"
                        maxlength="255"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                    >

                </div>

                <div>

                    <label
                        for="experience_years"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Teaching Experience (Years)
                    </label>

                    <input
                        id="experience_years"
                        type="number"
                        name="experience_years"
                        min="0"
                        max="60"
                        value="{{ old(
                            'experience_years',
                            $teacher->experience_years
                        ) }}"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                    >

                </div>

            </div>

            <div class="mt-5">

                <label
                    for="bio"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    About Teacher
                </label>

                <textarea
                    id="bio"
                    name="bio"
                    rows="5"
                    maxlength="3000"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                >{{ old(
                    'bio',
                    $teacher->bio
                ) }}</textarea>

            </div>

        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="text-lg font-bold text-slate-900">
                Subjects
            </h3>

            <div class="mt-6 space-y-6">

                @foreach($categoryOrder as $category)

                    @if(isset($subjectGroups[$category]))

                        <div class="overflow-hidden rounded-2xl border border-slate-200">

                            <div class="border-b border-slate-200 bg-slate-50 px-5 py-3">

                                <h4 class="font-bold text-slate-900">
                                    {{ $category }}
                                </h4>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $subjectGroups[$category]->count() }}
                                    subject(s)
                                </p>

                            </div>

                            <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">

                                @foreach($subjectGroups[$category] as $subject)

                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 transition hover:bg-indigo-50">

                                        <input
                                            type="checkbox"
                                            name="subjects[]"
                                            value="{{ $subject->id }}"
                                            @checked(
                                                in_array(
                                                    $subject->id,
                                                    $selectedSubjects
                                                )
                                            )
                                            class="h-4 w-4 rounded border-slate-300 text-indigo-600"
                                        >

                                        <span class="text-sm font-semibold text-slate-700">
                                            {{ $subject->name }}
                                        </span>

                                    </label>

                                @endforeach

                            </div>

                        </div>

                    @endif

                @endforeach

            </div>

        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="text-lg font-bold text-slate-900">
                Teaching Locations
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Select division and district, then choose one or more areas.
            </p>

            <div class="mt-5 grid gap-4 md:grid-cols-2">

                <div>

                    <label
                        for="teacher_location_division"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Division
                    </label>

                    <select
                        id="teacher_location_division"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
                    >
                        <option value="">
                            Select Division
                        </option>
                    </select>

                </div>

                <div>

                    <label
                        for="teacher_location_district"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        District
                    </label>

                    <select
                        id="teacher_location_district"
                        disabled
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm disabled:bg-slate-100 disabled:text-slate-400"
                    >
                        <option value="">
                            Select District
                        </option>
                    </select>

                </div>

            </div>

            <div class="mt-4 flex items-center justify-between gap-3">

                <p
                    id="teacher_location_hint"
                    class="text-sm text-slate-500"
                >
                    Select a division and district to view areas.
                </p>

                <span
                    id="teacher_location_selected_count"
                    class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700"
                >
                    {{ count($selectedLocations) }} selected
                </span>

            </div>

            <div
                id="teacher_location_list"
                class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
            >

                @foreach($locations as $location)

                    <label
                        data-location-card
                        data-division="{{ $location->division }}"
                        data-district="{{ $location->district }}"
                        class="hidden cursor-pointer items-start gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:bg-indigo-50"
                    >

                        <input
                            type="checkbox"
                            name="locations[]"
                            value="{{ $location->id }}"
                            @checked(
                                in_array(
                                    $location->id,
                                    $selectedLocations
                                )
                            )
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600"
                        >

                        <span class="text-sm">

                            <strong class="text-slate-800">
                                {{
                                    $location->area ===
                                    $location->district
                                        ? $location->area.' (Whole District)'
                                        : $location->area
                                }}
                            </strong>

                            <span class="mt-1 block text-xs text-slate-500">
                                {{ $location->district }},
                                {{ $location->division }}
                            </span>

                        </span>

                    </label>

                @endforeach

            </div>

        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="text-lg font-bold text-slate-900">
                Teaching Preferences
            </h3>

            <div class="mt-5 grid gap-5 md:grid-cols-3">

                <div>

                    <label
                        for="teaching_mode"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Teaching Mode
                    </label>

                    <select
                        id="teaching_mode"
                        name="teaching_mode"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
                    >

                        @foreach([
                            'offline' => 'Offline',
                            'online' => 'Online',
                            'both' => 'Both',
                        ] as $value => $label)

                            <option
                                value="{{ $value }}"
                                @selected(
                                    old(
                                        'teaching_mode',
                                        $teacher->teaching_mode
                                    ) === $value
                                )
                            >
                                {{ $label }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div>

                    <label
                        for="expected_salary_min"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Minimum Expected Salary
                    </label>

                    <input
                        id="expected_salary_min"
                        type="number"
                        name="expected_salary_min"
                        min="0"
                        max="9999999"
                        step="0.01"
                        value="{{ old(
                            'expected_salary_min',
                            $teacher->expected_salary_min
                        ) }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                    >

                </div>

                <div>

                    <label
                        for="expected_salary_max"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Maximum Expected Salary
                    </label>

                    <input
                        id="expected_salary_max"
                        type="number"
                        name="expected_salary_max"
                        min="0"
                        max="9999999"
                        step="0.01"
                        value="{{ old(
                            'expected_salary_max',
                            $teacher->expected_salary_max
                        ) }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                    >

                </div>

            </div>

            <label class="mt-6 flex cursor-pointer items-start gap-3 rounded-xl bg-slate-50 p-4">

                <input
                    type="checkbox"
                    name="is_available"
                    value="1"
                    @checked(
                        old(
                            'is_available',
                            $teacher->is_available
                        )
                    )
                    class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600"
                >

                <span>

                    <strong class="text-slate-800">
                        Available for tuition
                    </strong>

                    <span class="mt-1 block text-sm text-slate-500">
                        Controls whether this teacher is currently available in the marketplace.
                    </span>

                </span>

            </label>

        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

            <a
                href="{{ route('admin.teachers.index') }}"
                class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="rounded-xl bg-indigo-600 px-7 py-3 text-sm font-bold text-white hover:bg-indigo-700"
            >
                Save Teacher Profile
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const divisionSelect =
        document.getElementById(
            'teacher_location_division'
        );

    const districtSelect =
        document.getElementById(
            'teacher_location_district'
        );

    const cards =
        Array.from(
            document.querySelectorAll(
                '[data-location-card]'
            )
        );

    const hint =
        document.getElementById(
            'teacher_location_hint'
        );

    const selectedCount =
        document.getElementById(
            'teacher_location_selected_count'
        );

    const uniqueSorted = values =>
        [...new Set(
            values.filter(Boolean)
        )].sort(
            (a, b) =>
                a.localeCompare(b)
        );

    const setOptions = (
        select,
        placeholder,
        values
    ) => {

        select.innerHTML = '';

        select.append(
            new Option(
                placeholder,
                ''
            )
        );

        values.forEach(
            value => {

                select.append(
                    new Option(
                        value,
                        value
                    )
                );

            }
        );
    };

    const divisions =
        uniqueSorted(
            cards.map(
                card =>
                    card.dataset.division
            )
        );

    setOptions(
        divisionSelect,
        'Select Division',
        divisions
    );

    const updateSelectedCount = () => {

        const count =
            cards.filter(
                card =>
                    card.querySelector(
                        'input[type="checkbox"]'
                    )?.checked
            ).length;

        selectedCount.textContent =
            `${count} selected`;
    };

    const filterCards = () => {

        const division =
            divisionSelect.value;

        const district =
            districtSelect.value;

        cards.forEach(
            card => {

                const visible =
                    division &&
                    district &&
                    card.dataset.division ===
                        division &&
                    card.dataset.district ===
                        district;

                card.classList.toggle(
                    'hidden',
                    ! visible
                );

                card.classList.toggle(
                    'flex',
                    visible
                );

            }
        );

        if (
            ! division ||
            ! district
        ) {

            hint.textContent =
                'Select a division and district to view areas.';

            return;
        }

        const visibleCount =
            cards.filter(
                card =>
                    card.dataset.division ===
                        division &&
                    card.dataset.district ===
                        district
            ).length;

        hint.textContent =
            `${visibleCount} location option(s) available in ${district}.`;
    };

    divisionSelect.addEventListener(
        'change',
        () => {

            const division =
                divisionSelect.value;

            if (! division) {

                setOptions(
                    districtSelect,
                    'Select District',
                    []
                );

                districtSelect.disabled =
                    true;

                filterCards();

                return;
            }

            const districts =
                uniqueSorted(
                    cards
                        .filter(
                            card =>
                                card.dataset.division ===
                                division
                        )
                        .map(
                            card =>
                                card.dataset.district
                        )
                );

            setOptions(
                districtSelect,
                'Select District',
                districts
            );

            districtSelect.disabled =
                false;

            filterCards();

        }
    );

    districtSelect.addEventListener(
        'change',
        filterCards
    );

    cards.forEach(
        card => {

            const checkbox =
                card.querySelector(
                    'input[type="checkbox"]'
                );

            checkbox?.addEventListener(
                'change',
                updateSelectedCount
            );

        }
    );

    const firstSelectedCard =
        cards.find(
            card =>
                card.querySelector(
                    'input[type="checkbox"]'
                )?.checked
        );

    if (firstSelectedCard) {

        divisionSelect.value =
            firstSelectedCard
                .dataset
                .division;

        divisionSelect.dispatchEvent(
            new Event('change')
        );

        districtSelect.value =
            firstSelectedCard
                .dataset
                .district;

        filterCards();

    }

    updateSelectedCount();

});
</script>

@endsection