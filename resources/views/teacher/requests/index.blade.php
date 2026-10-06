@extends('layouts.app')

@section('title', 'Incoming Requests - Tuition Hub')

@php
    $pageTitle = 'Incoming Requests';
    $pageSubtitle = 'Teacher Marketplace';
@endphp

@section('content')

    <div>

        <h2 class="text-2xl font-black text-slate-900">
            Incoming Teacher Requests
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Review direct requests sent by students or guardians.
        </p>

    </div>

    <div class="mt-6 space-y-5">

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
                                {{ $teacherRequest->student?->name ?? 'Student / Guardian' }}
                            </h3>

                            <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $statusClasses }}">
                                {{ strtoupper($teacherRequest->status) }}
                            </span>

                            @if($teacherRequest->confirmed_at)

                                <span class="rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-bold text-indigo-700">
                                    CONFIRMED
                                </span>

                            @endif

                        </div>

                        @if($teacherRequest->tuitionPost)

                            <div class="mt-5 rounded-2xl border border-indigo-100 bg-indigo-50 p-5">

                                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500">
                                    Linked Tuition
                                </p>

                                <h4 class="mt-2 font-bold text-indigo-950">
                                    {{ $teacherRequest->tuitionPost->title }}
                                </h4>

                                <p class="mt-1 text-xs text-indigo-600">
                                    {{ $teacherRequest->tuitionPost->tuition_code }}
                                </p>

                                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                                    <div>

                                        <p class="text-xs text-indigo-500">
                                            Class
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-indigo-950">
                                            {{ $teacherRequest->tuitionPost->class_level }}
                                        </p>

                                    </div>

                                    <div>

                                        <p class="text-xs text-indigo-500">
                                            Location
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-indigo-950">
                                            {{ $teacherRequest->tuitionPost->location?->area ?? 'Not specified' }}
                                        </p>

                                    </div>

                                    <div>

                                        <p class="text-xs text-indigo-500">
                                            Salary
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-indigo-950">

                                            @if($teacherRequest->tuitionPost->salary !== null)

                                                ৳{{ number_format(
                                                    (float) $teacherRequest->tuitionPost->salary
                                                ) }}

                                            @else

                                                Negotiable

                                            @endif

                                        </p>

                                    </div>

                                    <div>

                                        <p class="text-xs text-indigo-500">
                                            Teaching Mode
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-indigo-950">
                                            {{ ucfirst($teacherRequest->tuitionPost->teaching_mode) }}
                                        </p>

                                    </div>

                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">

                                    @foreach($teacherRequest->tuitionPost->subjects as $subject)

                                        <span class="rounded-lg bg-white px-2.5 py-1 text-xs font-medium text-indigo-700">
                                            {{ $subject->name }}
                                        </span>

                                    @endforeach

                                </div>

                            </div>

                        @else

                            <div class="mt-5 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                                This is a general teacher request and is not linked to a specific tuition post.
                            </div>

                        @endif

                        @if($teacherRequest->message)

                            <div class="mt-5">

                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Message
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">
                                    {{ $teacherRequest->message }}
                                </p>

                            </div>

                        @endif

                        @if($teacherRequest->status === 'accepted')

                            <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">

                                <h4 class="font-bold text-emerald-900">
                                    Student / Guardian Contact
                                </h4>

                                <div class="mt-3 grid gap-3 sm:grid-cols-2">

                                    <div>

                                        <p class="text-xs text-emerald-600">
                                            Email
                                        </p>

                                        <p class="mt-1 break-all text-sm font-semibold text-emerald-900">
                                            {{ $teacherRequest->student?->email ?? 'N/A' }}
                                        </p>

                                    </div>

                                    <div>

                                        <p class="text-xs text-emerald-600">
                                            Phone
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-emerald-900">
                                            {{ $teacherRequest->student?->phone ?? 'N/A' }}
                                        </p>

                                    </div>

                                </div>

                            </div>

                        @else

                            <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                                Contact details will be unlocked after you accept this request.
                            </div>

                        @endif

                        @if($teacherRequest->confirmed_at)

                            <div class="mt-5 rounded-xl bg-indigo-50 p-4 text-sm text-indigo-700">

                                Student confirmed you for this tuition on

                                <strong>
                                    {{ $teacherRequest->confirmed_at->format('d M Y, h:i A') }}
                                </strong>.

                            </div>

                        @endif

                        <p class="mt-5 text-xs text-slate-400">
                            Sent:
                            {{ $teacherRequest->created_at->format('d M Y, h:i A') }}
                        </p>

                    </div>

                    <div class="lg:w-52">

                        @if($teacherRequest->status === 'pending')

                            <form
                                method="POST"
                                action="{{ route(
                                    'teacher.requests.accept',
                                    $teacherRequest
                                ) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Accept this teacher request?')"
                                    class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700"
                                >
                                    Accept
                                </button>

                            </form>

                            <form
                                method="POST"
                                action="{{ route(
                                    'teacher.requests.reject',
                                    $teacherRequest
                                ) }}"
                                class="mt-3"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Reject this teacher request?')"
                                    class="w-full rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                                >
                                    Reject
                                </button>

                            </form>

                        @elseif($teacherRequest->confirmed_at)

                            <div class="rounded-xl bg-indigo-50 p-4 text-center text-sm font-bold text-indigo-700">
                                Confirmed Teacher
                            </div>

                        @elseif($teacherRequest->status === 'accepted')

                            <div class="rounded-xl bg-emerald-50 p-4 text-center text-sm font-semibold text-emerald-700">
                                Accepted

                                <span class="mt-1 block text-xs font-normal">
                                    Waiting for student confirmation
                                </span>
                            </div>

                        @elseif($teacherRequest->status === 'rejected')

                            <div class="rounded-xl bg-rose-50 p-4 text-center text-sm font-semibold text-rose-700">
                                Rejected
                            </div>

                        @else

                            <div class="rounded-xl bg-slate-100 p-4 text-center text-sm font-semibold text-slate-600">
                                Cancelled
                            </div>

                        @endif

                    </div>

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

                <h3 class="text-lg font-bold text-slate-800">
                    No teacher requests yet
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Requests sent by students or guardians will appear here.
                </p>

            </div>

        @endforelse

    </div>

    @if(
        method_exists($requests, 'hasPages') &&
        $requests->hasPages()
    )

        <div class="mt-8">
            {{ $requests->links() }}
        </div>

    @endif

@endsection