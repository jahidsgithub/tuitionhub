@extends('layouts.app')

@section('title', 'Post Tuition - Tuition Hub')

@php
    $pageTitle = 'Post Tuition';
    $pageSubtitle = 'Student Marketplace';

    $subjectGroups = $subjects->groupBy('category');

    $categoryOrder = [
        'General',
        'Science',
        'Business Studies',
        'Humanities',
        'Other',
    ];

    $locationOptions = $locations
        ->map(function ($location) {
            return [
                'id' => $location->id,
                'division' => $location->division,
                'district' => $location->district,
                'area' => $location->area,
            ];
        })
        ->values();
@endphp

@section('content')

<div class="mx-auto max-w-5xl">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h2 class="text-2xl font-black text-slate-900">
                Post a Tuition Requirement
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Add your tuition details and submit them for admin review.
            </p>
        </div>

        <a
            href="{{ route('student.tuitions.index') }}"
            class="inline-flex justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
        >
            My Tuition Posts
        </a>

    </div>

    <form
        method="POST"
        action="{{ route('student.tuitions.store') }}"
        class="mt-6 space-y-5"
    >
        @csrf

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

            <h3 class="text-lg font-bold text-slate-900">
                Basic Information
            </h3>

            <div class="mt-5 grid gap-5 md:grid-cols-2">

                <div class="md:col-span-2">

                    <label
                        for="title"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Tuition Title
                    </label>

                    <input
                        id="title"
                        type="text"
                        name="title"
                        value="{{ old('title') }}"
                        required
                        maxlength="255"
                        placeholder="Example: Class 8 Math & Science Tutor Needed"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                </div>

                <div>

                    <label
                        for="class_level"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Class / Level
                    </label>

                    <input
                        id="class_level"
                        type="text"
                        name="class_level"
                        value="{{ old('class_level') }}"
                        required
                        maxlength="100"
                        placeholder="Example: Class 8"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                </div>

                <div>

                    <label
                        for="medium"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Medium
                    </label>

                    <select
                        id="medium"
                        name="medium"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                        <option value="">
                            Select Medium
                        </option>

                        <option
                            value="bangla"
                            @selected(old('medium') === 'bangla')
                        >
                            Bangla Medium
                        </option>

                        <option
                            value="english"
                            @selected(old('medium') === 'english')
                        >
                            English Medium
                        </option>

                        <option
                            value="english_version"
                            @selected(old('medium') === 'english_version')
                        >
                            English Version
                        </option>

                        <option
                            value="madrasa"
                            @selected(old('medium') === 'madrasa')
                        >
                            Madrasa
                        </option>

                        <option
                            value="other"
                            @selected(old('medium') === 'other')
                        >
                            Other
                        </option>

                    </select>

                </div>

            </div>

        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

            <h3 class="text-lg font-bold text-slate-900">
                Student & Teacher Preference
            </h3>

            <div class="mt-5 grid gap-5 md:grid-cols-2">

                <div>

                    <label
                        for="student_gender"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Student Gender
                    </label>

                    <select
                        id="student_gender"
                        name="student_gender"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
                    >
                        <option value="">
                            Not specified
                        </option>

                        <option
                            value="male"
                            @selected(old('student_gender') === 'male')
                        >
                            Male
                        </option>

                        <option
                            value="female"
                            @selected(old('student_gender') === 'female')
                        >
                            Female
                        </option>

                        <option
                            value="other"
                            @selected(old('student_gender') === 'other')
                        >
                            Other
                        </option>

                    </select>

                </div>

                <div>

                    <label
                        for="preferred_teacher_gender"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Preferred Teacher Gender
                    </label>

                    <select
                        id="preferred_teacher_gender"
                        name="preferred_teacher_gender"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
                    >
                        <option value="">
                            Select Preference
                        </option>

                        <option
                            value="any"
                            @selected(old('preferred_teacher_gender') === 'any')
                        >
                            Any
                        </option>

                        <option
                            value="male"
                            @selected(old('preferred_teacher_gender') === 'male')
                        >
                            Male
                        </option>

                        <option
                            value="female"
                            @selected(old('preferred_teacher_gender') === 'female')
                        >
                            Female
                        </option>

                    </select>

                </div>

            </div>

        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

            <h3 class="text-lg font-bold text-slate-900">
                Location & Tuition Details
            </h3>

            <div class="mt-5">

                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    Location
                </label>

                <div class="grid gap-4 md:grid-cols-3">

                    <select
                        id="location_division"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
                    >
                        <option value="">
                            Select Division
                        </option>
                    </select>

                    <select
                        id="location_district"
                        disabled
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm disabled:bg-slate-100 disabled:text-slate-400"
                    >
                        <option value="">
                            Select District
                        </option>
                    </select>

                    <select
                        id="location_id"
                        name="location_id"
                        disabled
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm disabled:bg-slate-100 disabled:text-slate-400"
                    >
                        <option value="">
                            Select Upazila / Area
                        </option>
                    </select>

                </div>

            </div>

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
                        <option value="">
                            Select Mode
                        </option>

                        <option
                            value="offline"
                            @selected(old('teaching_mode') === 'offline')
                        >
                            Offline
                        </option>

                        <option
                            value="online"
                            @selected(old('teaching_mode') === 'online')
                        >
                            Online
                        </option>

                        <option
                            value="both"
                            @selected(old('teaching_mode') === 'both')
                        >
                            Both
                        </option>

                    </select>

                </div>

                <div>

                    <label
                        for="days_per_week"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Days Per Week
                    </label>

                    <input
                        id="days_per_week"
                        type="number"
                        name="days_per_week"
                        min="1"
                        max="7"
                        value="{{ old('days_per_week') }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                    >

                </div>

                <div>

                    <label
                        for="salary"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Monthly Salary
                    </label>

                    <input
                        id="salary"
                        type="number"
                        name="salary"
                        min="0"
                        max="9999999"
                        step="0.01"
                        value="{{ old('salary') }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                    >

                </div>

            </div>

        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

            <div>

                <h3 class="text-lg font-bold text-slate-900">
                    Subjects
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Select one or more subjects from the relevant academic groups.
                </p>

            </div>

            <div class="mt-6 space-y-6">

                @foreach($categoryOrder as $category)

                    @if(isset($subjectGroups[$category]))

                        <div class="overflow-hidden rounded-2xl border border-slate-200">

                            <div class="border-b border-slate-200 bg-slate-50 px-5 py-3">

                                <h4 class="font-bold text-slate-900">
                                    {{ $category }}
                                </h4>

                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $subjectGroups[$category]->count() }}
                                    subject(s)
                                </p>

                            </div>

                            <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">

                                @foreach($subjectGroups[$category] as $subject)

                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 transition hover:border-indigo-300 hover:bg-indigo-50">

                                        <input
                                            type="checkbox"
                                            name="subjects[]"
                                            value="{{ $subject->id }}"
                                            @checked(
                                                in_array(
                                                    $subject->id,
                                                    old('subjects', [])
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

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

            <label
                for="requirements"
                class="text-lg font-bold text-slate-900"
            >
                Additional Requirements
            </label>

            <textarea
                id="requirements"
                name="requirements"
                rows="6"
                maxlength="5000"
                placeholder="Describe schedule preference, teacher experience, special requirements, etc."
                class="mt-5 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
            >{{ old('requirements') }}</textarea>

        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

            <a
                href="{{ route('student.tuitions.index') }}"
                class="inline-flex justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="rounded-xl bg-indigo-600 px-7 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
            >
                Submit Tuition
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const locations =
        {{ Illuminate\Support\Js::from($locationOptions) }};

    const divisionSelect =
        document.getElementById(
            'location_division'
        );

    const districtSelect =
        document.getElementById(
            'location_district'
        );

    const areaSelect =
        document.getElementById(
            'location_id'
        );

    const selectedLocationId =
        {{ Illuminate\Support\Js::from(old('location_id')) }};

    const uniqueSorted = (values) =>
        [...new Set(
            values.filter(Boolean)
        )].sort(
            (a, b) =>
                a.localeCompare(b)
        );

    const setOptions = (
        select,
        placeholder,
        items
    ) => {

        select.innerHTML = '';

        select.append(
            new Option(
                placeholder,
                ''
            )
        );

        items.forEach(
            (item) => {

                select.append(
                    new Option(
                        item.label,
                        item.value
                    )
                );

            }
        );
    };

    const divisions =
        uniqueSorted(
            locations.map(
                (location) =>
                    location.division
            )
        );

    setOptions(
        divisionSelect,
        'Select Division',
        divisions.map(
            (division) => ({
                value: division,
                label: division,
            })
        )
    );

    const loadDistricts = (
        selectedDistrict = ''
    ) => {

        const division =
            divisionSelect.value;

        setOptions(
            districtSelect,
            'Select District',
            []
        );

        setOptions(
            areaSelect,
            'Select Upazila / Area',
            []
        );

        areaSelect.disabled = true;

        if (! division) {

            districtSelect.disabled = true;

            return;
        }

        const districts =
            uniqueSorted(
                locations
                    .filter(
                        (location) =>
                            location.division ===
                            division
                    )
                    .map(
                        (location) =>
                            location.district
                    )
            );

        setOptions(
            districtSelect,
            'Select District',
            districts.map(
                (district) => ({
                    value: district,
                    label: district,
                })
            )
        );

        districtSelect.disabled = false;

        districtSelect.value =
            selectedDistrict || '';
    };

    const loadAreas = (
        selectedId = ''
    ) => {

        const division =
            divisionSelect.value;

        const district =
            districtSelect.value;

        setOptions(
            areaSelect,
            'Select Upazila / Area',
            []
        );

        if (
            ! division ||
            ! district
        ) {

            areaSelect.disabled = true;

            return;
        }

        const areas =
            locations
                .filter(
                    (location) =>
                        location.division ===
                            division &&
                        location.district ===
                            district
                )
                .sort(
                    (a, b) =>
                        a.area.localeCompare(
                            b.area
                        )
                )
                .map(
                    (location) => ({
                        value:
                            String(
                                location.id
                            ),

                        label:
                            location.area ===
                            location.district
                                ? `${location.area} (Whole District)`
                                : location.area,
                    })
                );

        setOptions(
            areaSelect,
            'Select Upazila / Area',
            areas
        );

        areaSelect.disabled = false;

        areaSelect.value =
            selectedId
                ? String(selectedId)
                : '';
    };

    divisionSelect.addEventListener(
        'change',
        () => {

            loadDistricts();
            loadAreas();

        }
    );

    districtSelect.addEventListener(
        'change',
        () => {

            loadAreas();

        }
    );

    if (selectedLocationId) {

        const selectedLocation =
            locations.find(
                (location) =>
                    String(location.id) ===
                    String(selectedLocationId)
            );

        if (selectedLocation) {

            divisionSelect.value =
                selectedLocation.division;

            loadDistricts(
                selectedLocation.district
            );

            districtSelect.value =
                selectedLocation.district;

            loadAreas(
                selectedLocation.id
            );
        }
    }

});
</script>

@endsection