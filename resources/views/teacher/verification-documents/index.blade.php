@extends('layouts.app')

@section('title', 'Verification Documents - Tuition Hub')

@php
    $pageTitle = 'Verification Documents';
    $pageSubtitle = 'Teacher Verification';
@endphp

@section('content')

    <div class="mx-auto max-w-5xl">

        <div>

            <h2 class="text-2xl font-black text-slate-900">
                Teacher Verification Documents
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Upload documents securely for admin verification.
            </p>

        </div>

        <div class="mt-5 rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-sm leading-6 text-indigo-800">
            Your verification files are private. They are accessible only to
            you and authorized administrators.
        </div>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="text-lg font-bold text-slate-900">
                Upload Document
            </h3>

            <form
                method="POST"
                action="{{ route(
                    'teacher.verification-documents.store'
                ) }}"
                enctype="multipart/form-data"
                class="mt-5"
            >
                @csrf

                <div class="grid gap-5 md:grid-cols-2">

                    <div>

                        <label
                            for="document_type"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Document Type
                        </label>

                        <select
                            id="document_type"
                            name="document_type"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >
                            <option value="">
                                Select Type
                            </option>

                            <option
                                value="nid"
                                @selected(old('document_type') === 'nid')
                            >
                                National ID
                            </option>

                            <option
                                value="student_id"
                                @selected(old('document_type') === 'student_id')
                            >
                                Student ID
                            </option>

                            <option
                                value="certificate"
                                @selected(old('document_type') === 'certificate')
                            >
                                Certificate
                            </option>

                            <option
                                value="other"
                                @selected(old('document_type') === 'other')
                            >
                                Other
                            </option>
                        </select>

                    </div>

                    <div>

                        <label
                            for="document_name"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Document Name
                        </label>

                        <input
                            id="document_name"
                            type="text"
                            name="document_name"
                            value="{{ old('document_name') }}"
                            placeholder="Optional description"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >

                    </div>

                </div>

                <div class="mt-5">

                    <label
                        for="file"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        File
                    </label>

                    <input
                        id="file"
                        type="file"
                        name="file"
                        required
                        accept=".jpg,.jpeg,.png,.webp,.pdf"
                        class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"
                    >

                    <p class="mt-2 text-xs text-slate-500">
                        JPG, PNG, WEBP or PDF. Maximum 5 MB.
                    </p>

                </div>

                <button
                    type="submit"
                    class="mt-5 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Upload Securely
                </button>

            </form>

        </section>

        <section class="mt-7">

            <h3 class="text-xl font-black text-slate-900">
                Submitted Documents
            </h3>

            <div class="mt-4 space-y-4">

                @forelse($documents as $document)

                    @php
                        $documentStatusClass = match($document->status) {
                            'approved' => 'bg-emerald-50 text-emerald-700',
                            'rejected' => 'bg-rose-50 text-rose-700',
                            default => 'bg-amber-50 text-amber-700',
                        };
                    @endphp

                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                        <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">

                            <div>

                                <div class="flex flex-wrap items-center gap-2">

                                    <h4 class="font-bold text-slate-900">
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
                                    </h4>

                                    <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $documentStatusClass }}">
                                        {{ strtoupper($document->status) }}
                                    </span>

                                </div>

                                <p class="mt-2 text-sm text-slate-500">
                                    Type:
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

                                <p class="mt-1 text-sm text-slate-500">
                                    Uploaded:
                                    {{
                                        $document->created_at
                                            ?->format('d M Y, h:i A')
                                    }}
                                </p>

                                @if($document->admin_note)

                                    <div class="mt-4 rounded-xl border border-rose-100 bg-rose-50 p-4">

                                        <p class="text-sm font-bold text-rose-700">
                                            Admin Note
                                        </p>

                                        <p class="mt-1 text-sm text-rose-700">
                                            {{ $document->admin_note }}
                                        </p>

                                    </div>

                                @endif

                            </div>

                            <div class="flex flex-wrap gap-2">

                                <a
                                    href="{{ route(
                                        'teacher.verification-documents.view',
                                        $document
                                    ) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                >
                                    View Document
                                </a>

                                @if($document->status !== 'approved')

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'teacher.verification-documents.destroy',
                                            $document
                                        ) }}"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            onclick="return confirm('Delete this verification document?')"
                                            class="rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                @endif

                            </div>

                        </div>

                    </article>

                @empty

                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-500">
                        No verification documents uploaded yet.
                    </div>

                @endforelse

            </div>

        </section>

    </div>

@endsection