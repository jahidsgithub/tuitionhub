@extends('layouts.app')

@section('title', 'Find Teachers - Tuition Hub')

@php
    $pageTitle = 'Find Teachers';
    $pageSubtitle = 'Student Marketplace';

    $preferredCategoryOrder = [
        'Science',
        'Business Studies',
        'Humanities',
        'General',
        'Other',
    ];

    $orderedCategories = collect($preferredCategoryOrder)
        ->filter(fn ($category) => $categories->contains($category))
        ->concat(
            $categories->filter(
                fn ($category) => ! in_array(
                    $category,
                    $preferredCategoryOrder,
                    true
                )
            )
        )
        ->values();
@endphp

@section('content')

    <section class="rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 p-6 text-white sm:p-8">

        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <p class="text-sm font-semibold text-indigo-100">
                    Teacher Marketplace
                </p>

                <h2 class="mt-2 text-3xl font-black">
                    Find the right teacher
                </h2>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-indigo-100">
                    Search verified and available teachers by subject group,
                    subject, location, gender and teaching mode.
                </p>
            </div>

            <a
                href="{{ route('student.teacher-requests.index') }}"
                class="inline-flex justify-center rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/20"
            >
                My Teacher Requests
            </a>

        </div>

    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

        <div class="mb-5">
            <h3 class="text-lg font-bold text-slate-900">
                Search Filters
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Narrow down teachers using academic group and location.
            </p>
        </div>

        <form
            method="GET"
            action="{{ route('student.teachers.index') }}"
            class="space-y-5"
        >

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">

                <div class="md:col-span-2 xl:col-span-2">

                    <label
                        for="search"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Search
                    </label>

                    <input
                        id="search"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Teacher name, university, department or subject"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                </div>

                <div>

                    <label
                        for="category"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Subject Group
                    </label>

                    <select
                        id="category"
                        name="category"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                        <option value="">
                            All Groups
                        </option>

                        @foreach($orderedCategories as $category)
                            <option
                                value="{{ $category }}"
                                @selected(request('category') === $category)
                            >
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>

                </div>

                <div>

                    <label
                        for="subject"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Subject
                    </label>

                    <select
                        id="subject"
                        name="subject"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                        <option value="">
                            All Subjects
                        </option>
                    </select>

                </div>

            </div>

            <div class="border-t border-slate-100 pt-5">

                <p class="mb-3 text-sm font-bold text-slate-800">
                    Location
                </p>

                <div class="grid gap-4 md:grid-cols-3">

                    <div>

                        <label
                            for="division"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Division
                        </label>

                        <select
                            id="division"
                            name="division"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >
                            <option value="">
                                All Divisions
                            </option>
                        </select>

                    </div>

                    <div>

                        <label
                            for="district"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            District
                        </label>

                        <select
                            id="district"
                            name="district"
                            disabled
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm disabled:bg-slate-100 disabled:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >
                            <option value="">
                                All Districts
                            </option>
                        </select>

                    </div>

                    <div>

                        <label
                            for="location"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Upazila / Area
                        </label>

                        <select
                            id="location"
                            name="location"
                            disabled
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm disabled:bg-slate-100 disabled:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >
                            <option value="">
                                All Areas
                            </option>
                        </select>

                    </div>

                </div>

            </div>

            <div class="grid gap-4 border-t border-slate-100 pt-5 md:grid-cols-2">

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
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                        <option value="">Any Gender</option>

                        <option
                            value="male"
                            @selected(request('gender') === 'male')
                        >
                            Male
                        </option>

                        <option
                            value="female"
                            @selected(request('gender') === 'female')
                        >
                            Female
                        </option>

                        <option
                            value="other"
                            @selected(request('gender') === 'other')
                        >
                            Other
                        </option>
                    </select>

                </div>

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
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >
                        <option value="">Any Mode</option>

                        <option
                            value="offline"
                            @selected(request('teaching_mode') === 'offline')
                        >
                            Offline
                        </option>

                        <option
                            value="online"
                            @selected(request('teaching_mode') === 'online')
                        >
                            Online
                        </option>

                        <option
                            value="both"
                            @selected(request('teaching_mode') === 'both')
                        >
                            Both
                        </option>
                    </select>

                </div>

            </div>

            <div class="flex flex-wrap gap-3 border-t border-slate-100 pt-5">

                <button
                    type="submit"
                    class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Search Teachers
                </button>

                @if(
                    request()->filled('search') ||
                    request()->filled('category') ||
                    request()->filled('subject') ||
                    request()->filled('division') ||
                    request()->filled('district') ||
                    request()->filled('location') ||
                    request()->filled('gender') ||
                    request()->filled('teaching_mode')
                )

                    <a
                        href="{{ route('student.teachers.index') }}"
                        class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                    >
                        Clear Filters
                    </a>

                @endif

            </div>

        </form>

    </section>

    <section class="mt-6">

        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">

            <div>
                <h3 class="text-xl font-bold text-slate-900">
                    Available Teachers
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $teachers->total() }} teacher(s) found
                </p>
            </div>

            @if(request()->filled('category'))
                <span class="w-fit rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700">
                    {{ request('category') }}
                </span>
            @endif

        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">

            @forelse($teachers as $teacher)

                @php
                    $teacherSubjectGroups = $teacher->subjects
                        ->groupBy('category');
                @endphp

                <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow-md">

                    <div class="flex items-start gap-4">

                        @if($teacher->profile_photo)
                            <img
                                src="{{ asset('storage/'.$teacher->profile_photo) }}"
                                alt="{{ $teacher->user?->name ?? 'Teacher' }}"
                                class="h-14 w-14 shrink-0 rounded-2xl border border-slate-200 object-cover"
                            >
                        @else
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-lg font-black text-indigo-700">
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

                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-2">

                                <h4 class="truncate text-lg font-bold text-slate-900">
                                    {{ $teacher->user?->name ?? 'Teacher' }}
                                </h4>

                                @if($teacher->is_verified)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase text-emerald-700">
                                        Verified
                                    </span>
                                @endif

                            </div>

                            <p class="mt-1 truncate text-sm text-slate-500">
                                {{ $teacher->university ?? 'University not specified' }}
                            </p>

                            @if($teacher->department)
                                <p class="mt-0.5 truncate text-xs text-slate-400">
                                    {{ $teacher->department }}
                                </p>
                            @endif

                        </div>

                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">

                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs text-slate-400">
                                Experience
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $teacher->experience_years ?? 0 }} year(s)
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs text-slate-400">
                                Mode
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{
                                    ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $teacher->teaching_mode ?? 'Not specified'
                                        )
                                    )
                                }}
                            </p>
                        </div>

                    </div>

                    <div class="mt-5">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Subjects
                        </p>

                        @forelse($teacherSubjectGroups as $group => $groupSubjects)

                            <div class="mt-3">

                                <p class="text-xs font-bold text-slate-600">
                                    {{ $group ?: 'General' }}
                                </p>

                                <div class="mt-1.5 flex flex-wrap gap-2">

                                    @foreach($groupSubjects->take(3) as $subject)
                                        <span class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">
                                            {{ $subject->name }}
                                        </span>
                                    @endforeach

                                    @if($groupSubjects->count() > 3)
                                        <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs text-slate-500">
                                            +{{ $groupSubjects->count() - 3 }}
                                        </span>
                                    @endif

                                </div>

                            </div>

                        @empty

                            <p class="mt-2 text-sm text-slate-400">
                                No subjects specified
                            </p>

                        @endforelse

                    </div>

                    <div class="mt-5">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Preferred Areas
                        </p>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{
                                $teacher->locations
                                    ->map(
                                        fn ($location) =>
                                            $location->area
                                            .' - '
                                            .$location->district
                                    )
                                    ->take(3)
                                    ->join(', ')
                                ?: 'Not specified'
                            }}
                        </p>

                        @if($teacher->locations->count() > 3)
                            <p class="mt-1 text-xs font-semibold text-indigo-600">
                                +{{ $teacher->locations->count() - 3 }} more location(s)
                            </p>
                        @endif

                    </div>

                    <div class="mt-auto pt-6">

                        <a
                            href="{{ route(
                                'student.teachers.show',
                                $teacher
                            ) }}"
                            class="flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-indigo-600"
                        >
                            View Teacher
                        </a>

                    </div>

                </article>

            @empty

                <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

                    <h3 class="font-bold text-slate-800">
                        No teachers found
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Try changing the subject group, subject, location or other filters.
                    </p>

                    <a
                        href="{{ route('student.teachers.index') }}"
                        class="mt-5 inline-flex rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700"
                    >
                        Clear All Filters
                    </a>

                </div>

            @endforelse

        </div>

        @if($teachers->hasPages())

            <div class="mt-8">
                {{ $teachers->links() }}
            </div>

        @endif

    </section>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Subject Group -> Subject
    |--------------------------------------------------------------------------
    */

    const subjects = @json(
        $subjects->map(fn ($subject) => [
            'id' => $subject->id,
            'name' => $subject->name,
            'category' => $subject->category,
        ])->values()
    );

    const categorySelect =
        document.getElementById('category');

    const subjectSelect =
        document.getElementById('subject');

    const selectedSubject = @json(
        request('subject')
    );

    const setSubjectOptions = () => {
        const category = categorySelect.value;

        const filteredSubjects = subjects
            .filter(
                (subject) =>
                    ! category ||
                    subject.category === category
            )
            .sort(
                (a, b) =>
                    a.name.localeCompare(b.name)
            );

        subjectSelect.innerHTML = '';
        subjectSelect.append(
            new Option('All Subjects', '')
        );

        filteredSubjects.forEach((subject) => {
            subjectSelect.append(
                new Option(
                    subject.name,
                    String(subject.id)
                )
            );
        });
    };

    categorySelect.addEventListener(
        'change',
        () => {
            setSubjectOptions();
            subjectSelect.value = '';
        }
    );

    setSubjectOptions();

    if (selectedSubject) {
        subjectSelect.value =
            String(selectedSubject);
    }


    /*
    |--------------------------------------------------------------------------
    | Division -> District -> Upazila / Area
    |--------------------------------------------------------------------------
    */

    const locations = @json(
        $locations->map(fn ($location) => [
            'id' => $location->id,
            'division' => $location->division,
            'district' => $location->district,
            'area' => $location->area,
        ])->values()
    );

    const divisionSelect =
        document.getElementById('division');

    const districtSelect =
        document.getElementById('district');

    const locationSelect =
        document.getElementById('location');

    const selectedDivision = @json(
        request('division')
    );

    const selectedDistrict = @json(
        request('district')
    );

    const selectedLocation = @json(
        request('location')
    );

    const uniqueSorted = (values) =>
        [...new Set(values.filter(Boolean))]
            .sort(
                (a, b) =>
                    a.localeCompare(b)
            );

    const replaceOptions = (
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

        values.forEach((value) => {
            select.append(
                new Option(
                    value.label,
                    value.value
                )
            );
        });
    };

    const divisions = uniqueSorted(
        locations.map(
            (location) =>
                location.division
        )
    );

    replaceOptions(
        divisionSelect,
        'All Divisions',
        divisions.map((division) => ({
            value: division,
            label: division,
        }))
    );

    const loadDistricts = (
        districtToSelect = ''
    ) => {
        const division =
            divisionSelect.value;

        if (! division) {
            replaceOptions(
                districtSelect,
                'All Districts',
                []
            );

            replaceOptions(
                locationSelect,
                'All Areas',
                []
            );

            districtSelect.disabled = true;
            locationSelect.disabled = true;

            return;
        }

        const districts = uniqueSorted(
            locations
                .filter(
                    (location) =>
                        location.division === division
                )
                .map(
                    (location) =>
                        location.district
                )
        );

        replaceOptions(
            districtSelect,
            'All Districts',
            districts.map((district) => ({
                value: district,
                label: district,
            }))
        );

        districtSelect.disabled = false;
        districtSelect.value = districtToSelect;
    };

    const loadLocations = (
        locationToSelect = ''
    ) => {
        const division =
            divisionSelect.value;

        const district =
            districtSelect.value;

        if (! division || ! district) {
            replaceOptions(
                locationSelect,
                'All Areas',
                []
            );

            locationSelect.disabled = true;

            return;
        }

        const areas = locations
            .filter(
                (location) =>
                    location.division === division &&
                    location.district === district
            )
            .sort(
                (a, b) =>
                    a.area.localeCompare(b.area)
            )
            .map((location) => ({
                value: String(location.id),
                label:
                    location.area === location.district
                        ? `${location.area} (Whole District)`
                        : location.area,
            }));

        replaceOptions(
            locationSelect,
            'All Areas',
            areas
        );

        locationSelect.disabled = false;
        locationSelect.value = locationToSelect
            ? String(locationToSelect)
            : '';
    };

    divisionSelect.addEventListener(
        'change',
        () => {
            loadDistricts();
            loadLocations();
        }
    );

    districtSelect.addEventListener(
        'change',
        () => {
            loadLocations();
        }
    );

    if (selectedDivision) {
        divisionSelect.value =
            selectedDivision;

        loadDistricts(
            selectedDistrict ?? ''
        );

        if (selectedDistrict) {
            districtSelect.value =
                selectedDistrict;

            loadLocations(
                selectedLocation ?? ''
            );
        }
    } elseif (selectedLocation) {
        const currentLocation =
            locations.find(
                (location) =>
                    String(location.id) ===
                    String(selectedLocation)
            );

        if (currentLocation) {
            divisionSelect.value =
                currentLocation.division;

            loadDistricts(
                currentLocation.district
            );

            districtSelect.value =
                currentLocation.district;

            loadLocations(
                currentLocation.id
            );
        }
    }
});
</script>

@endsection
