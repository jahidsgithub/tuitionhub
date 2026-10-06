@extends('layouts.app')



@section('title', 'Teacher Profile - Tuition Hub')



@php

    $pageTitle = 'Teacher Profile';

    $pageSubtitle = 'Find Teachers';

@endphp



@section('content')



    <div class="mb-5">



        <a

            href="{{ route('student.teachers.index') }}"

            class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700"

        >

            ← Back to Teachers

        </a>



    </div>



    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">



        <div class="space-y-6">



            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">



                <div class="flex flex-col gap-5 sm:flex-row sm:items-start">



                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl bg-indigo-50 text-2xl font-black text-indigo-700">

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



                    <div class="min-w-0 flex-1">



                        <div class="flex flex-wrap items-center gap-2">



                            <h1 class="text-2xl font-black text-slate-900 sm:text-3xl">

                                {{ $teacher->user?->name }}

                            </h1>



                            @if($teacher->is_verified)



                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">

                                    VERIFIED

                                </span>



                            @endif



                            @if($teacher->is_available)



                                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">

                                    AVAILABLE

                                </span>



                            @endif



                        </div>



                        <p class="mt-3 font-medium text-slate-600">

                            {{ $teacher->university ?? 'University not specified' }}

                        </p>



                        @if($teacher->department)



                            <p class="mt-1 text-sm text-slate-500">

                                {{ $teacher->department }}

                            </p>



                        @endif



                    </div>



                </div>



                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">



                    <div class="rounded-2xl bg-slate-50 p-4">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">

                            Degree

                        </p>



                        <p class="mt-2 font-semibold text-slate-800">

                            {{ $teacher->degree ?? 'Not specified' }}

                        </p>

                    </div>



                    <div class="rounded-2xl bg-slate-50 p-4">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">

                            Experience

                        </p>



                        <p class="mt-2 font-semibold text-slate-800">

                            {{ $teacher->experience_years ?? 0 }} year(s)

                        </p>

                    </div>



                    <div class="rounded-2xl bg-slate-50 p-4">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">

                            Gender

                        </p>



                        <p class="mt-2 font-semibold text-slate-800">

                            {{

                                $teacher->gender

                                    ? ucfirst($teacher->gender)

                                    : 'Not specified'

                            }}

                        </p>

                    </div>



                    <div class="rounded-2xl bg-slate-50 p-4">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">

                            Teaching Mode

                        </p>



                        <p class="mt-2 font-semibold text-slate-800">

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



            </section>



            <section class="grid gap-6 md:grid-cols-2">



                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">



                    <h2 class="font-bold text-slate-900">

                        Subjects

                    </h2>



                    <div class="mt-4 flex flex-wrap gap-2">



                        @forelse($teacher->subjects as $subject)



                            <span class="rounded-xl bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700">

                                {{ $subject->name }}

                            </span>



                        @empty



                            <p class="text-sm text-slate-500">

                                Not specified

                            </p>



                        @endforelse



                    </div>



                </div>



                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">



                    <h2 class="font-bold text-slate-900">

                        Preferred Areas

                    </h2>



                    <div class="mt-4 flex flex-wrap gap-2">



                        @forelse($teacher->locations as $location)



                            <span class="rounded-xl bg-slate-100 px-3 py-1.5 text-sm text-slate-700">

                                {{ $location->area }}

                            </span>



                        @empty



                            <p class="text-sm text-slate-500">

                                Not specified

                            </p>



                        @endforelse



                    </div>



                </div>



            </section>



            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">



                <h2 class="font-bold text-slate-900">

                    Expected Salary

                </h2>



                <p class="mt-3 text-lg font-bold text-slate-800">



                    @if(

                        $teacher->expected_salary_min ||

                        $teacher->expected_salary_max

                    )



                        ৳{{ number_format((float) ($teacher->expected_salary_min ?? 0)) }}

                        -

                        ৳{{ number_format((float) ($teacher->expected_salary_max ?? 0)) }}



                    @else



                        Negotiable



                    @endif



                </p>



            </section>



            @if($teacher->bio)



                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">



                    <h2 class="font-bold text-slate-900">

                        About Teacher

                    </h2>



                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">

                        {{ $teacher->bio }}

                    </p>



                </section>



            @endif



        </div>



        <aside>



            <div class="sticky top-28 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">



                <h2 class="text-xl font-black text-slate-900">

                    Request this Teacher

                </h2>



                <p class="mt-2 text-sm leading-6 text-slate-500">

                    Select one of your published tuition posts and send a request to this teacher.

                </p>



                @if($existingRequest)



                    <div class="mt-5 rounded-2xl border border-indigo-200 bg-indigo-50 p-4">



                        <p class="text-sm text-indigo-700">

                            You already have a

                            <strong>

                                {{ strtoupper($existingRequest->status) }}

                            </strong>

                            request with this teacher.

                        </p>



                        <a

                            href="{{ route('student.teacher-requests.index') }}"

                            class="mt-4 inline-flex text-sm font-bold text-indigo-700"

                        >

                            View Requests →

                        </a>



                    </div>



                @elseif($tuitionPosts->isEmpty())

                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4">

                        <p class="text-sm font-semibold text-amber-800">
                            Create a published tuition post first
                        </p>

                        <p class="mt-2 text-sm leading-6 text-amber-700">
                            A teacher request must be linked to one of your published tuition posts so an assignment can be created after the teacher accepts and you confirm.
                        </p>

                        <a
                            href="{{ route('student.tuitions.create') }}"
                            class="mt-4 inline-flex rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-amber-700"
                        >
                            Create Tuition Post
                        </a>

                    </div>

                @else



                    <form

                        method="POST"

                        action="{{ route(

                            'student.teachers.request',

                            $teacher

                        ) }}"

                        class="mt-6 space-y-5"

                    >

                        @csrf



                        <div>



                            <label

                                for="tuition_post_id"

                                class="mb-2 block text-sm font-semibold text-slate-700"

                            >

                                Select Tuition Post

                            </label>



                            <select

                                id="tuition_post_id"

                                name="tuition_post_id"

                                required

                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"

                            >

                                <option value="" disabled @selected(! old('tuition_post_id'))>

                                    Select one of your published tuition posts

                                </option>



                                @foreach($tuitionPosts as $tuition)



                                    <option

                                        value="{{ $tuition->id }}"

                                        @selected(

                                            old('tuition_post_id') == $tuition->id

                                        )

                                    >

                                        {{ $tuition->tuition_code }}

                                        - {{ $tuition->title }}

                                    </option>



                                @endforeach

                            </select>

                            @error('tuition_post_id')
                                <p class="mt-2 text-sm text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror

                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                The selected tuition will be used to create the final assignment after the teacher accepts and you confirm.
                            </p>



                        </div>



                        <div>



                            <label

                                for="message"

                                class="mb-2 block text-sm font-semibold text-slate-700"

                            >

                                Message

                            </label>



                            <textarea

                                id="message"

                                name="message"

                                rows="5"

                                placeholder="Write a short message to the teacher..."

                                class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"

                            >{{ old('message') }}</textarea>



                        </div>



                        <button

                            type="submit"

                            class="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"

                        >

                            Send Teacher Request

                        </button>



                    </form>



                @endif



            </div>



        </aside>



    </div>



@endsection