@extends('layouts.app')

@section('title', 'User Management - Tuition Hub Admin')

@php
    $pageTitle = 'User Management';
    $pageSubtitle = 'Admin Panel';
@endphp

@section('content')

    <div>

        <h2 class="text-2xl font-black text-slate-900">
            User Management
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Search and manage Teacher and Student / Guardian accounts.
        </p>

    </div>

    <form
        method="GET"
        action="{{ route('admin.users.index') }}"
        class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >

        <div class="grid gap-4 md:grid-cols-4">

            <div>

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
                    placeholder="Name, email or phone..."
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                >

            </div>

            <div>

                <label
                    for="role"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Role
                </label>

                <select
                    id="role"
                    name="role"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                >
                    <option value="">
                        All Roles
                    </option>

                    <option
                        value="teacher"
                        @selected(request('role') === 'teacher')
                    >
                        Teacher
                    </option>

                    <option
                        value="student"
                        @selected(request('role') === 'student')
                    >
                        Student / Guardian
                    </option>
                </select>

            </div>

            <div>

                <label
                    for="status"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                >
                    <option value="">
                        All Statuses
                    </option>

                    <option
                        value="active"
                        @selected(request('status') === 'active')
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        @selected(request('status') === 'inactive')
                    >
                        Inactive
                    </option>

                    <option
                        value="suspended"
                        @selected(request('status') === 'suspended')
                    >
                        Suspended
                    </option>
                </select>

            </div>

            <div class="flex items-end gap-2">

                <button
                    type="submit"
                    class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Search
                </button>

                @if(
                    request('search') ||
                    request('role') ||
                    request('status')
                )

                    <a
                        href="{{ route('admin.users.index') }}"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                    >
                        Clear
                    </a>

                @endif

            </div>

        </div>

    </form>

    <div class="mt-6 space-y-5">

        @forelse($users as $user)

            @php
                $statusClass = match($user->status) {
                    'active' => 'bg-emerald-50 text-emerald-700',
                    'inactive' => 'bg-amber-50 text-amber-700',
                    'suspended' => 'bg-rose-50 text-rose-700',
                    default => 'bg-slate-100 text-slate-600',
                };

                $roleClass = $user->role === 'teacher'
                    ? 'bg-indigo-50 text-indigo-700'
                    : 'bg-violet-50 text-violet-700';
            @endphp

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <div class="flex flex-col gap-6 xl:flex-row xl:justify-between">

                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-2">

                            <h3 class="text-xl font-bold text-slate-900">
                                {{ $user->name }}
                            </h3>

                            <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $roleClass }}">
                                {{
                                    $user->role === 'teacher'
                                        ? 'TEACHER'
                                        : 'STUDENT / GUARDIAN'
                                }}
                            </span>

                            <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $statusClass }}">
                                {{ strtoupper($user->status) }}
                            </span>

                            @if(
                                $user->role === 'teacher' &&
                                $user->teacherProfile?->is_verified
                            )

                                <span class="rounded-full bg-sky-50 px-3 py-1 text-[10px] font-bold text-sky-700">
                                    VERIFIED
                                </span>

                            @endif

                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                            <div>

                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Email
                                </p>

                                <p class="mt-1 break-all text-sm font-semibold text-slate-800">
                                    {{ $user->email }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Phone
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $user->phone ?? 'N/A' }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Registered
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $user->created_at->format('d M Y') }}
                                </p>

                            </div>

                        </div>

                        @if($user->role === 'teacher')

                            <div class="mt-5 rounded-2xl bg-slate-50 p-5">

                                <h4 class="font-bold text-slate-900">
                                    Teacher Profile
                                </h4>

                                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                                    <div>

                                        <p class="text-xs text-slate-400">
                                            University
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-slate-700">
                                            {{ $user->teacherProfile?->university ?? 'Not specified' }}
                                        </p>

                                    </div>

                                    <div>

                                        <p class="text-xs text-slate-400">
                                            Department
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-slate-700">
                                            {{ $user->teacherProfile?->department ?? 'Not specified' }}
                                        </p>

                                    </div>

                                    <div>

                                        <p class="text-xs text-slate-400">
                                            Experience
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-slate-700">
                                            {{ $user->teacherProfile?->experience_years ?? 0 }}
                                            year(s)
                                        </p>

                                    </div>

                                </div>

                                <div class="mt-4">

                                    <p class="text-xs uppercase tracking-wide text-slate-400">
                                        Subjects
                                    </p>

                                    <div class="mt-2 flex flex-wrap gap-2">

                                        @forelse(
                                            $user->teacherProfile?->subjects ?? collect()
                                            as $subject
                                        )

                                            <span class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs text-slate-700">
                                                {{ $subject->name }}
                                            </span>

                                        @empty

                                            <span class="text-sm text-slate-500">
                                                No subjects selected.
                                            </span>

                                        @endforelse

                                    </div>

                                </div>

                            </div>

                        @else

                            <div class="mt-5 rounded-2xl bg-slate-50 p-5">

                                <h4 class="font-bold text-slate-900">
                                    Student / Guardian Profile
                                </h4>

                                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                                    <div>

                                        <p class="text-xs text-slate-400">
                                            Guardian
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-slate-700">
                                            {{ $user->studentProfile?->guardian_name ?? 'Not specified' }}
                                        </p>

                                    </div>

                                    <div>

                                        <p class="text-xs text-slate-400">
                                            Class
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-slate-700">
                                            {{ $user->studentProfile?->class_level ?? 'Not specified' }}
                                        </p>

                                    </div>

                                    <div>

                                        <p class="text-xs text-slate-400">
                                            Medium
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-slate-700">
                                            {{
                                                $user->studentProfile?->medium
                                                    ? ucwords(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $user->studentProfile->medium
                                                        )
                                                    )
                                                    : 'Not specified'
                                            }}
                                        </p>

                                    </div>

                                </div>

                            </div>

                        @endif

                    </div>

                    <div class="space-y-3 xl:w-52">

                        @if($user->status !== 'active')

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.users.activate',
                                    $user
                                ) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Activate this account?')"
                                    class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700"
                                >
                                    Activate
                                </button>

                            </form>

                        @endif

                        @if($user->status !== 'inactive')

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.users.inactive',
                                    $user
                                ) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Mark this account as inactive?')"
                                    class="w-full rounded-xl border border-amber-200 bg-white px-4 py-3 text-sm font-semibold text-amber-700 transition hover:bg-amber-50"
                                >
                                    Set Inactive
                                </button>

                            </form>

                        @endif

                        @if($user->status !== 'suspended')

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.users.suspend',
                                    $user
                                ) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    onclick="return confirm('Suspend this account? The user will no longer be able to use protected areas of the platform.')"
                                    class="w-full rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                                >
                                    Suspend
                                </button>

                            </form>

                        @endif

                        @if($user->role === 'teacher')

                            <a
                                href="{{ route(
                                    'admin.teachers.index',
                                    [
                                        'search' => $user->email,
                                    ]
                                ) }}"
                                class="block w-full rounded-xl bg-indigo-50 px-4 py-3 text-center text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100"
                            >
                                Verification
                            </a>

                        @endif

                    </div>

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center text-sm text-slate-500">
                No users found.
            </div>

        @endforelse

    </div>

    @if($users->hasPages())

        <div class="mt-8">
            {{ $users->links() }}
        </div>

    @endif

@endsection