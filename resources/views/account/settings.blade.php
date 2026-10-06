@extends('layouts.app')

@section('title', 'Account Settings - Tuition Hub')

@php
    $pageTitle = 'Account Settings';
    $pageSubtitle = 'Manage Account';
@endphp

@section('content')

    <div class="mx-auto max-w-2xl">

        <div class="flex items-center justify-between gap-4">

            <div>

                <h2 class="text-2xl font-black text-slate-900">
                    Account Settings
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Update your basic account information.
                </p>

            </div>

            <a
                href="{{ route('account.security.edit') }}"
                class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                Security
            </a>

        </div>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">

                <div class="flex flex-wrap items-center gap-3">

                    <span class="text-sm font-semibold text-slate-700">
                        Email Status
                    </span>

                    @if(auth()->user()->hasVerifiedEmail())

                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold text-emerald-700">
                            VERIFIED
                        </span>

                    @else

                        <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-bold text-amber-700">
                            NOT VERIFIED
                        </span>

                    @endif

                </div>

                <p class="mt-3 text-xs leading-5 text-slate-500">
                    Changing your email address will require email verification again.
                </p>

            </div>

            <form
                method="POST"
                action="{{ route('account.settings.update') }}"
                class="mt-6 space-y-5"
            >
                @csrf
                @method('PUT')

                <div>

                    <label
                        for="name"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Full Name
                    </label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old(
                            'name',
                            auth()->user()->name
                        ) }}"
                        required
                        autocomplete="name"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                    @error('name')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <div>

                    <label
                        for="email"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Email Address
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old(
                            'email',
                            auth()->user()->email
                        ) }}"
                        required
                        autocomplete="email"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                    @error('email')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <div>

                    <label
                        for="phone"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Phone Number
                    </label>

                    <input
                        id="phone"
                        type="text"
                        name="phone"
                        value="{{ old(
                            'phone',
                            auth()->user()->phone
                        ) }}"
                        required
                        autocomplete="tel"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                    @error('phone')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <div class="border-t border-slate-100 pt-5">

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

                    <p class="mt-2 text-xs text-slate-500">
                        Required to confirm these account changes.
                    </p>

                    @error('current_password')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Save Account Changes
                </button>

            </form>

        </section>

    </div>

@endsection