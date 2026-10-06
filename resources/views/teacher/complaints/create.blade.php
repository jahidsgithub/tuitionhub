@extends('layouts.app')

@section('title', 'Submit Complaint - Tuition Hub')

@php
    $pageTitle = 'Submit Complaint';
    $pageSubtitle = 'Teacher Support';
@endphp

@section('content')

    <div class="mx-auto max-w-3xl">

        <a
            href="{{ route('teacher.assignments.index') }}"
            class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
        >
            ← Back to Assignments
        </a>

        <div class="mt-5">

            <h2 class="text-2xl font-black text-slate-900">
                Submit Complaint
            </h2>

            <p class="mt-2 text-sm text-slate-500">
                Tuition:
                <strong class="text-slate-700">
                    {{ $assignment->tuitionPost?->title }}
                </strong>
            </p>

            <p class="mt-1 text-sm text-slate-500">
                Student:
                <strong class="text-slate-700">
                    {{ $assignment->student?->name }}
                </strong>
            </p>

        </div>

        <form
            method="POST"
            action="{{ route(
                'teacher.complaints.store',
                $assignment
            ) }}"
            class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
        >
            @csrf

            <div>

                <label
                    for="category"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Category
                </label>

                <select
                    id="category"
                    name="category"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                >
                    <option value="">
                        Select category
                    </option>

                    <option
                        value="behavior"
                        @selected(old('category') === 'behavior')
                    >
                        Behavior
                    </option>

                    <option
                        value="payment"
                        @selected(old('category') === 'payment')
                    >
                        Payment
                    </option>

                    <option
                        value="attendance"
                        @selected(old('category') === 'attendance')
                    >
                        Attendance
                    </option>

                    <option
                        value="misinformation"
                        @selected(old('category') === 'misinformation')
                    >
                        Misinformation
                    </option>

                    <option
                        value="harassment"
                        @selected(old('category') === 'harassment')
                    >
                        Harassment
                    </option>

                    <option
                        value="safety"
                        @selected(old('category') === 'safety')
                    >
                        Safety Concern
                    </option>

                    <option
                        value="other"
                        @selected(old('category') === 'other')
                    >
                        Other
                    </option>
                </select>

            </div>

            <div class="mt-5">

                <label
                    for="subject"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Subject
                </label>

                <input
                    id="subject"
                    type="text"
                    name="subject"
                    value="{{ old('subject') }}"
                    required
                    placeholder="Short summary of the issue"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                >

            </div>

            <div class="mt-5">

                <label
                    for="description"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="7"
                    required
                    placeholder="Describe the issue clearly..."
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                >{{ old('description') }}</textarea>

            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <a
                    href="{{ route('teacher.assignments.index') }}"
                    class="inline-flex justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-rose-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-rose-700"
                >
                    Submit Complaint
                </button>

            </div>

        </form>

    </div>

@endsection