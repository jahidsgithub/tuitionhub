@extends('layouts.app')

@section('title', 'My Teacher Requests - Tuition Hub')

@php
    $pageTitle = 'Teacher Requests';
    $pageSubtitle = 'Student Marketplace';
@endphp

@section('content')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <h2 class="text-2xl font-black text-slate-900">
                My Teacher Requests
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Track direct requests sent to teachers.
            </p>

        </div>

        <a
            href="{{ route('student.teachers.index') }}"
            class="inline-flex justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
        >
            Find Teachers
        </a>

    </div>

    <div class="mt-6 space-y-4">

        @forelse($requests as $teacherRequest)

            @php
                $statusClasses = match($teacherRequest->status) {
                    'accepted' => 'bg-emerald-50 text-emerald-700',
                    'rejected' => 'bg-rose-50 text-rose-700',
                    'cancelled' => 'bg-slate-100 text-slate-600',
                    default => 'bg-amber-50 text-amber-700',
                };
            @endphp

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <div class="flex flex-col gap-6 lg:flex-row lg:justify-between">

                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-2">

                            <h3 class="text-xl font-bold text-slate-900">
                                {{
                                    $teacherRequest
                                        ->teacherProfile
                                        ?->user
                                        ?->name
                                    ?? 'Teacher'
                                }}
                            </h3>

                            @if($teacherRequest->teacherProfile?->is_verified)

                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">
                                    VERIFIED
                                </span>

                            @endif

                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $statusClasses }}">
                                {{ strtoupper($teacherRequest->status) }}
                            </span>

                            @if($teacherRequest->confirmed_at)

                                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-bold text-indigo-700">
                                    CONFIRMED TEACHER
                                </span>

                            @endif

                        </div>

                        <p class="mt-2 text-sm text-slate-500">
                            {{
                                $teacherRequest
                                    ->teacherProfile
                                    ?->university
                                ?? 'Institution not specified'
                            }}
                        </p>

                        @if($teacherRequest->tuitionPost)

                            <div class="mt-5 rounded-2xl bg-slate-50 p-4">

                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Linked Tuition
                                </p>

                                <p class="mt-2 font-bold text-slate-800">
                                    {{ $teacherRequest->tuitionPost->title }}
                                </p>

                                <div class="mt-2 flex flex-wrap gap-3 text-xs text-slate-500">

                                    <span>
                                        {{ $teacherRequest->tuitionPost->tuition_code }}
                                    </span>

                                    <span>
                                        {{ strtoupper($teacherRequest->tuitionPost->status) }}
                                    </span>

                                </div>

                            </div>

                        @else

                            <div class="mt-5 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                                General teacher request
                            </div>

                        @endif

                        @if($teacherRequest->message)

                            <div class="mt-5">

                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Your Message
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">
                                    {{ $teacherRequest->message }}
                                </p>

                            </div>

                        @endif

                        @if($teacherRequest->status === 'accepted')

                            <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">

                                <p class="font-bold text-emerald-900">
                                    Teacher Contact Unlocked
                                </p>

                                <div class="mt-3 grid gap-3 sm:grid-cols-2">

                                    <div>
                                        <p class="text-xs text-emerald-600">
                                            Email
                                        </p>

                                        <p class="mt-1 break-all text-sm font-semibold text-emerald-900">
                                            {{
                                                $teacherRequest
                                                    ->teacherProfile
                                                    ?->user
                                                    ?->email
                                                ?? 'N/A'
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-emerald-600">
                                            Phone
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-emerald-900">
                                            {{
                                                $teacherRequest
                                                    ->teacherProfile
                                                    ?->user
                                                    ?->phone
                                                ?? 'N/A'
                                            }}
                                        </p>
                                    </div>

                                </div>

                            </div>

                        @else

                            <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                                Contact information becomes available after the teacher accepts your request.
                            </div>

                        @endif

                    </div>

                    <div class="lg:w-56">

                        <div class="rounded-xl px-3 py-3 text-center text-sm font-bold {{ $statusClasses }}">
                            {{ strtoupper($teacherRequest->status) }}
                        </div>

                        @if(
                            $teacherRequest->status === 'accepted' &&
                            $teacherRequest->tuition_post_id &&
                            ! $teacherRequest->confirmed_at
                        )

                            <form
                                method="POST"
                                action="{{ route(
                                    'student.teacher-requests.confirm',
                                    $teacherRequest
                                ) }}"
                                class="mt-3"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Confirm this teacher for the linked tuition? This will mark the tuition as filled.')"
                                    class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                                >
                                    Confirm Teacher
                                </button>

                            </form>

                        @endif

                        @if($teacherRequest->confirmed_at)

                            <div class="mt-3 rounded-xl bg-indigo-50 p-3 text-center text-sm text-indigo-700">

                                Confirmed

                                <strong class="mt-1 block">
                                    {{ $teacherRequest->confirmed_at->format('d M Y') }}
                                </strong>

                            </div>

                        @endif

                        @if($teacherRequest->status === 'pending')

                            <form
                                method="POST"
                                action="{{ route(
                                    'student.teacher-requests.cancel',
                                    $teacherRequest
                                ) }}"
                                class="mt-3"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Cancel this teacher request?')"
                                    class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                                >
                                    Cancel Request
                                </button>

                            </form>

                        @endif

                    </div>

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

                <h3 class="font-bold text-slate-800">
                    No teacher requests yet
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Browse teachers and send your first direct request.
                </p>

                <a
                    href="{{ route('student.teachers.index') }}"
                    class="mt-5 inline-flex rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white"
                >
                    Find Teachers
                </a>

            </div>

        @endforelse

    </div>

    @if($requests->hasPages())

        <div class="mt-8">
            {{ $requests->links() }}
        </div>

    @endif

@endsection