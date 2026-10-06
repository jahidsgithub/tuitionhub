@extends('layouts.app')

@section('title', 'Verification Documents - Tuition Hub Admin')

@php
    $pageTitle = 'Verification Documents';
    $pageSubtitle = 'Admin Panel';
@endphp

@section('content')

    <div>

        <h2 class="text-2xl font-black text-slate-900">
            Teacher Verification Documents
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Securely review documents submitted by teachers.
        </p>

    </div>

    <form
        method="GET"
        action="{{ route(
            'admin.verification-documents.index'
        ) }}"
        class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >

        <div class="grid gap-4 md:grid-cols-4">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Teacher / document..."
                class="rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >

            <select
                name="status"
                class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >
                <option value="">
                    All Status
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

            <select
                name="type"
                class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >
                <option value="">
                    All Types
                </option>

                <option
                    value="nid"
                    @selected(request('type') === 'nid')
                >
                    NID
                </option>

                <option
                    value="student_id"
                    @selected(request('type') === 'student_id')
                >
                    Student ID
                </option>

                <option
                    value="certificate"
                    @selected(request('type') === 'certificate')
                >
                    Certificate
                </option>

                <option
                    value="other"
                    @selected(request('type') === 'other')
                >
                    Other
                </option>
            </select>

            <div class="flex gap-2">

                <button
                    type="submit"
                    class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Filter
                </button>

                @if(
                    request()->filled('search') ||
                    request()->filled('status') ||
                    request()->filled('type')
                )

                    <a
                        href="{{ route('admin.verification-documents.index') }}"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600"
                    >
                        Clear
                    </a>

                @endif

            </div>

        </div>

    </form>

    <div class="mt-6 space-y-5">

        @forelse($documents as $document)

            @php
                $statusClass = match($document->status) {
                    'approved' => 'bg-emerald-50 text-emerald-700',
                    'rejected' => 'bg-rose-50 text-rose-700',
                    default => 'bg-amber-50 text-amber-700',
                };
            @endphp

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <div class="flex flex-col gap-6 xl:flex-row xl:justify-between">

                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-2">

                            <h3 class="text-xl font-bold text-slate-900">
                                {{
                                    $document
                                        ->teacherProfile
                                        ?->user
                                        ?->name
                                    ?? 'Unknown Teacher'
                                }}
                            </h3>

                            <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $statusClass }}">
                                {{ strtoupper($document->status) }}
                            </span>

                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">

                            <div>

                                <p class="text-xs text-slate-400">
                                    Email
                                </p>

                                <p class="mt-1 break-all text-sm font-semibold text-slate-800">
                                    {{
                                        $document
                                            ->teacherProfile
                                            ?->user
                                            ?->email
                                        ?? 'N/A'
                                    }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs text-slate-400">
                                    Document
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{
                                        $document->document_name
                                        ?: ucwords(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $document->document_type
                                            )
                                        )
                                    }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs text-slate-400">
                                    Type
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{
                                        ucwords(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $document->document_type
                                            )
                                        )
                                    }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs text-slate-400">
                                    Submitted
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{
                                        $document
                                            ->created_at
                                            ?->format('d M Y, h:i A')
                                    }}
                                </p>

                            </div>

                        </div>

                        @if($document->reviewer)

                            <p class="mt-4 text-sm text-slate-500">
                                Reviewed By:
                                <strong class="text-slate-700">
                                    {{ $document->reviewer->name }}
                                </strong>
                            </p>

                        @endif

                        @if($document->admin_note)

                            <div class="mt-4 rounded-xl bg-slate-50 p-4">

                                <p class="text-sm font-bold text-slate-800">
                                    Admin Note
                                </p>

                                <p class="mt-1 text-sm leading-6 text-slate-600">
                                    {{ $document->admin_note }}
                                </p>

                            </div>

                        @endif

                    </div>

                    <div class="space-y-3 xl:w-80">

                        <a
                            href="{{ route(
                                'admin.verification-documents.view',
                                $document
                            ) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Open Secure Document
                        </a>

                        @if($document->status !== 'approved')

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.verification-documents.approve',
                                    $document
                                ) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Approve this verification document?')"
                                    class="w-full rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700"
                                >
                                    Approve Document
                                </button>

                            </form>

                        @endif

                        @if($document->status === 'pending')

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.verification-documents.reject',
                                    $document
                                ) }}"
                                class="rounded-xl border border-rose-100 bg-rose-50 p-4"
                            >
                                @csrf
                                @method('PATCH')

                                <label
                                    for="admin_note_{{ $document->id }}"
                                    class="mb-2 block text-sm font-semibold text-rose-800"
                                >
                                    Rejection Reason
                                </label>

                                <textarea
                                    id="admin_note_{{ $document->id }}"
                                    name="admin_note"
                                    rows="3"
                                    required
                                    class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm focus:border-rose-400 focus:ring-4 focus:ring-rose-100"
                                ></textarea>

                                <button
                                    type="submit"
                                    onclick="return confirm('Reject this verification document?')"
                                    class="mt-3 w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-rose-700"
                                >
                                    Reject Document
                                </button>

                            </form>

                        @endif

                    </div>

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center text-sm text-slate-500">
                No verification documents found.
            </div>

        @endforelse

    </div>

    @if($documents->hasPages())

        <div class="mt-8">
            {{ $documents->links() }}
        </div>

    @endif

@endsection