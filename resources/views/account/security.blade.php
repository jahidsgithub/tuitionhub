@extends('layouts.app')

@section('title', 'Account Security - Tuition Hub')

@php
    $pageTitle = 'Account Security';
    $pageSubtitle = 'Manage Account';
@endphp

@section('content')

    <div class="mx-auto max-w-2xl">

        <div class="flex items-center justify-between gap-4">

            <div>

                <h2 class="text-2xl font-black text-slate-900">
                    Account Security
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Manage your password and active account sessions.
                </p>

            </div>

            <a
                href="{{ route('account.settings.edit') }}"
                class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                Account Settings
            </a>

        </div>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="text-lg font-bold text-slate-900">
                Account
            </h3>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">

                <div class="rounded-xl bg-slate-50 p-4">

                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Name
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ auth()->user()->name }}
                    </p>

                </div>

                <div class="rounded-xl bg-slate-50 p-4">

                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Role
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ ucfirst(auth()->user()->role) }}
                    </p>

                </div>

                <div class="rounded-xl bg-slate-50 p-4 sm:col-span-2">

                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Email
                    </p>

                    <p class="mt-1 break-all font-semibold text-slate-800">
                        {{ auth()->user()->email }}
                    </p>

                </div>

            </div>

        </section>

        <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">

            <h3 class="text-xl font-bold text-slate-900">
                Change Password
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Use a different password from your current password.
            </p>

            <form
                method="POST"
                action="{{ route('account.security.update') }}"
                class="mt-6 space-y-5"
            >
                @csrf
                @method('PUT')

                <input
                    type="hidden"
                    name="action"
                    value="change_password"
                >

                <div>

                    <label
                        for="current_password"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Current Password
                    </label>

                    <input
                        id="current_password"
                        type="password"
                        name="current_password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                    @error('current_password')

                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

                <div>

                    <label
                        for="password"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        New Password
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                    @error('password')

                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

                <div>

                    <label
                        for="password_confirmation"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Confirm New Password
                    </label>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Change Password
                </button>

            </form>

        </section>

        <section class="mt-5 rounded-2xl border border-rose-200 bg-white p-6 shadow-sm sm:p-7">

            <h3 class="text-xl font-bold text-slate-900">
                Other Logged-in Devices
            </h3>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                If your account is logged in on another browser or device,
                you can sign those sessions out.
            </p>

            <div class="mt-5 rounded-xl border border-rose-100 bg-rose-50 p-4 text-sm text-rose-700">
                Your current browser will remain logged in.
            </div>

            <form
                method="POST"
                action="{{ route('account.security.update') }}"
                class="mt-5"
            >
                @csrf
                @method('PUT')

                <input
                    type="hidden"
                    name="action"
                    value="logout_other_devices"
                >

                <label
                    for="device_password"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Confirm Your Password
                </label>

                <input
                    id="device_password"
                    type="password"
                    name="device_password"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-rose-500 focus:ring-4 focus:ring-rose-100"
                >

                @error('device_password')

                    <p class="mt-2 text-sm text-rose-600">
                        {{ $message }}
                    </p>

                @enderror

                <button
                    type="submit"
                    onclick="return confirm('Sign out all other logged-in devices?')"
                    class="mt-5 w-full rounded-xl bg-rose-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-rose-700"
                >
                    Logout Other Devices
                </button>

            </form>

        </section>

    </div>

@endsection