@extends('layouts.app')

@section('title', 'Student Profile - Tuition Hub')

@php
    $pageTitle = 'Student / Guardian Profile';
    $pageSubtitle = 'Account';
@endphp

@section('content')

    <div class="mx-auto max-w-5xl">

        <div>

            <h2 class="text-2xl font-black text-slate-900">
                Student / Guardian Profile
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Complete your profile information for a smoother tuition matching experience.
            </p>

        </div>

        <form
            method="POST"
            action="{{ route('student.profile.update') }}"
            enctype="multipart/form-data"
            class="mt-6 space-y-5"
        >
            @csrf
            @method('PUT')

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

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

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

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

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <h3 class="text-lg font-bold text-slate-900">
                    Student Information
                </h3>

                <div class="mt-5 grid gap-5 md:grid-cols-2">

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
                            value="{{ old(
                                'class_level',
                                $profile->class_level
                            ) }}"
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
                                @selected(
                                    old(
                                        'medium',
                                        $profile->medium
                                    ) === 'bangla'
                                )
                            >
                                Bangla Medium
                            </option>

                            <option
                                value="english"
                                @selected(
                                    old(
                                        'medium',
                                        $profile->medium
                                    ) === 'english'
                                )
                            >
                                English Medium
                            </option>

                            <option
                                value="english_version"
                                @selected(
                                    old(
                                        'medium',
                                        $profile->medium
                                    ) === 'english_version'
                                )
                            >
                                English Version
                            </option>

                            <option
                                value="madrasa"
                                @selected(
                                    old(
                                        'medium',
                                        $profile->medium
                                    ) === 'madrasa'
                                )
                            >
                                Madrasa
                            </option>

                            <option
                                value="other"
                                @selected(
                                    old(
                                        'medium',
                                        $profile->medium
                                    ) === 'other'
                                )
                            >
                                Other
                            </option>
                        </select>

                    </div>

                </div>

            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <h3 class="text-lg font-bold text-slate-900">
                    Guardian Information
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Fill these fields if the account is managed by a guardian.
                </p>

                <div class="mt-5 grid gap-5 md:grid-cols-2">

                    <div>

                        <label
                            for="guardian_name"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Guardian Name
                        </label>

                        <input
                            id="guardian_name"
                            type="text"
                            name="guardian_name"
                            value="{{ old(
                                'guardian_name',
                                $profile->guardian_name
                            ) }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >

                    </div>

                    <div>

                        <label
                            for="guardian_phone"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Guardian Phone
                        </label>

                        <input
                            id="guardian_phone"
                            type="text"
                            name="guardian_phone"
                            value="{{ old(
                                'guardian_phone',
                                $profile->guardian_phone
                            ) }}"
                            placeholder="01XXXXXXXXX"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >

                    </div>

                </div>

            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <h3 class="text-lg font-bold text-slate-900">
                    Address
                </h3>

                <textarea
                    name="address"
                    rows="4"
                    placeholder="Enter your area / address"
                    class="mt-5 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                >{{ old(
                    'address',
                    $profile->address
                ) }}</textarea>

            </section>

            <div class="flex justify-end">

                <button
                    type="submit"
                    class="rounded-xl bg-indigo-600 px-7 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Save Profile
                </button>

            </div>

        </form>

    </div>

@endsection