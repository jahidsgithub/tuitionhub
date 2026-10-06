@extends('layouts.app')

@section('title', 'Teacher Profile - Tuition Hub')

@php
    $pageTitle = 'Teacher Profile';
    $pageSubtitle = 'Teacher Account';

    $selectedSubjects = old(
        'subjects',
        $profile->subjects->pluck('id')->toArray()
    );

    $selectedLocations = old(
        'locations',
        $profile->locations->pluck('id')->toArray()
    );

    $subjectGroups = $subjects->groupBy('category');

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

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div>

            <h2 class="text-2xl font-black text-slate-900">
                Teacher Profile
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Complete your profile before joining the Tuition Hub marketplace.
            </p>

        </div>

        @if($profile->is_verified)

            <span class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700">
                Verified Teacher
            </span>

        @else

            <span class="rounded-full bg-amber-50 px-4 py-2 text-sm font-bold text-amber-700">
                Verification Pending
            </span>

        @endif

    </div>

    @if($profile->is_verified)

        <div class="mt-5 rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-sm leading-6 text-indigo-800">
            Your profile is verified. Changing your photo, education,
            subjects, locations, gender, experience or teaching mode will
            require admin verification again.
        </div>

    @endif

    <form
        method="POST"
        action="{{ route('teacher.profile.update') }}"
        enctype="multipart/form-data"
        class="mt-6 space-y-5"
    >
        @csrf
        @method('PUT')

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="text-lg font-bold text-slate-900">
                Profile Photo
            </h3>

            <div class="mt-5 flex flex-col gap-6 sm:flex-row sm:items-center">

                @if($profile->profile_photo)

                    <img
                        src="{{ asset(
                            'storage/'.$profile->profile_photo
                        ) }}"
                        alt="{{ $user->name }}"
                        class="h-28 w-28 rounded-2xl border border-slate-200 object-cover"
                    >

                @else

                    <div class="flex h-28 w-28 items-center justify-center rounded-2xl bg-indigo-50 text-3xl font-black text-indigo-700">
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

                <div class="flex-1">

                    <label
                        for="profile_photo"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Upload Photo
                    </label>

                    <input
                        id="profile_photo"
                        type="file"
                        name="profile_photo"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"
                    >

                    <p class="mt-2 text-xs text-slate-500">
                        JPG, PNG or WEBP. Maximum size 2 MB.
                    </p>

                </div>

            </div>

        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="text-lg font-bold text-slate-900">
                Basic Information
            </h3>

            <div class="mt-5 grid gap-5 md:grid-cols-2">

                <div>

                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Name
                    </label>

                    <input
                        type="text"
                        value="{{ $user->name }}"
                        disabled
                        class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-500"
                    >

                </div>

                <div>

                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Email
                    </label>

                    <input
                        type="email"
                        value="{{ $user->email }}"
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
                        value="{{ $user->phone }}"
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
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                        <option value="">
                            Select Gender
                        </option>

                        <option
                            value="male"
                            @selected(
                                old(
                                    'gender',
                                    $profile->gender
                                ) === 'male'
                            )
                        >
                            Male
                        </option>

                        <option
                            value="female"
                            @selected(
                                old(
                                    'gender',
                                    $profile->gender
                                ) === 'female'
                            )
                        >
                            Female
                        </option>

                        <option
                            value="other"
                            @selected(
                                old(
                                    'gender',
                                    $profile->gender
                                ) === 'other'
                            )
                        >
                            Other
                        </option>

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
                            $profile->university
                        ) }}"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
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
                            $profile->department
                        ) }}"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
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
                            $profile->degree
                        ) }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
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
                            $profile->experience_years
                        ) }}"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                </div>

            </div>

            <div class="mt-5">

                <label
                    for="bio"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    About You
                </label>

                <textarea
                    id="bio"
                    name="bio"
                    rows="5"
                    maxlength="3000"
                    placeholder="Write a short introduction about your teaching experience..."
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                >{{ old(
                    'bio',
                    $profile->bio
                ) }}</textarea>

            </div>

        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div>

                <h3 class="text-lg font-bold text-slate-900">
                    Subjects You Teach
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Select subjects from one or more academic groups.
                </p>

            </div>

            <div class="mt-6 space-y-6">

                @foreach($categoryOrder as $category)

                    @if(isset($subjectGroups[$category]))

                        <div class="overflow-hidden rounded-2xl border border-slate-200">

                            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-3">

                                <div>

                                    <h4 class="font-bold text-slate-900">
                                        {{ $category }}
                                    </h4>

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $subjectGroups[$category]->count() }}
                                        subject(s)
                                    </p>

                                </div>

                                <button
                                    type="button"
                                    data-subject-group-toggle="{{ $category }}"
                                    class="text-xs font-bold text-indigo-600 hover:text-indigo-700"
                                >
                                    Select All
                                </button>

                            </div>

                            <div
                                data-subject-group="{{ $category }}"
                                class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3"
                            >

                                @foreach($subjectGroups[$category] as $subject)

                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 transition hover:border-indigo-300 hover:bg-indigo-50">

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
                                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
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
                Preferred Teaching Locations
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Select division and district, then choose one or more upazila / areas.
                Your previous selections remain selected when you change district.
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
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
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
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm disabled:bg-slate-100 disabled:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                        <option value="">
                            Select District
                        </option>
                    </select>

                </div>

            </div>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <p
                    id="teacher_location_hint"
                    class="text-sm text-slate-500"
                >
                    Select a division and district to view available areas.
                </p>

                <span
                    id="teacher_location_selected_count"
                    class="w-fit rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700"
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
                        class="hidden cursor-pointer items-start gap-3 rounded-xl border border-slate-200 px-4 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/50"
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
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
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
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                        <option
                            value="offline"
                            @selected(
                                old(
                                    'teaching_mode',
                                    $profile->teaching_mode
                                ) === 'offline'
                            )
                        >
                            Offline
                        </option>

                        <option
                            value="online"
                            @selected(
                                old(
                                    'teaching_mode',
                                    $profile->teaching_mode
                                ) === 'online'
                            )
                        >
                            Online
                        </option>

                        <option
                            value="both"
                            @selected(
                                old(
                                    'teaching_mode',
                                    $profile->teaching_mode
                                ) === 'both'
                            )
                        >
                            Both
                        </option>

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
                        value="{{ old(
                            'expected_salary_min',
                            $profile->expected_salary_min
                        ) }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
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
                        value="{{ old(
                            'expected_salary_max',
                            $profile->expected_salary_max
                        ) }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
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
                            $profile->is_available
                        )
                    )
                    class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                >

                <span>

                    <strong class="text-slate-800">
                        Available for tuition
                    </strong>

                    <span class="mt-1 block text-sm text-slate-500">
                        Students can find and request you when your profile is verified.
                    </span>

                </span>

            </label>

        </section>

        <div class="flex justify-end">

            <button
                type="submit"
                class="rounded-xl bg-indigo-600 px-7 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-100 transition hover:bg-indigo-700"
            >
                Save Profile
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Subject Group Select All
    |--------------------------------------------------------------------------
    */

    const subjectGroupButtons =
        document.querySelectorAll(
            '[data-subject-group-toggle]'
        );

    subjectGroupButtons.forEach((button) => {

        const category =
            button.dataset.subjectGroupToggle;

        const group =
            document.querySelector(
                `[data-subject-group="${CSS.escape(category)}"]`
            );

        if (! group) {
            return;
        }

        const checkboxes = Array.from(
            group.querySelectorAll(
                'input[type="checkbox"][name="subjects[]"]'
            )
        );

        const updateButtonText = () => {

            const allChecked =
                checkboxes.length > 0 &&
                checkboxes.every(
                    (checkbox) =>
                        checkbox.checked
                );

            button.textContent =
                allChecked
                    ? 'Clear All'
                    : 'Select All';
        };

        button.addEventListener(
            'click',
            () => {

                const allChecked =
                    checkboxes.length > 0 &&
                    checkboxes.every(
                        (checkbox) =>
                            checkbox.checked
                    );

                checkboxes.forEach(
                    (checkbox) => {
                        checkbox.checked =
                            ! allChecked;
                    }
                );

                updateButtonText();
            }
        );

        checkboxes.forEach(
            (checkbox) => {
                checkbox.addEventListener(
                    'change',
                    updateButtonText
                );
            }
        );

        updateButtonText();
    });


    /*
    |--------------------------------------------------------------------------
    | Location Dependent Filter
    |--------------------------------------------------------------------------
    */

    const divisionSelect =
        document.getElementById(
            'teacher_location_division'
        );

    const districtSelect =
        document.getElementById(
            'teacher_location_district'
        );

    const cards = Array.from(
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

    const uniqueSorted = (values) =>
        [...new Set(
            values.filter(Boolean)
        )]
            .sort(
                (a, b) =>
                    a.localeCompare(b)
            );

    const divisions =
        uniqueSorted(
            cards.map(
                (card) =>
                    card.dataset.division
            )
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
            (value) => {
                select.append(
                    new Option(
                        value,
                        value
                    )
                );
            }
        );
    };

    setOptions(
        divisionSelect,
        'Select Division',
        divisions
    );

    const updateSelectedCount = () => {

        const count =
            cards.filter(
                (card) => {

                    const checkbox =
                        card.querySelector(
                            'input[type="checkbox"]'
                        );

                    return checkbox?.checked;
                }
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
            (card) => {

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
                'Select a division and district to view available areas.';

            return;
        }

        const visibleCount =
            cards.filter(
                (card) =>
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
                            (card) =>
                                card.dataset.division ===
                                division
                        )
                        .map(
                            (card) =>
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
        (card) => {

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
            (card) => {

                const checkbox =
                    card.querySelector(
                        'input[type="checkbox"]'
                    );

                return checkbox?.checked;
            }
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