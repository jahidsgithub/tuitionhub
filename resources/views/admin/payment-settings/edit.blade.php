@extends('layouts.app')

@section('title', 'Payment Settings - Tuition Hub Admin')

@php
    $pageTitle = 'Payment Settings';
    $pageSubtitle = 'Admin Panel';
@endphp

@section('content')

    <div class="mx-auto max-w-4xl">

        <div>
            <h2 class="text-2xl font-black text-slate-900">
                Payment Settings
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Configure the payment accounts teachers will use for subscription payments.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('admin.payment-settings.update') }}"
            class="mt-6 space-y-5"
        >
            @csrf
            @method('PUT')

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <h3 class="text-xl font-bold text-slate-900">
                            bKash
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Configure bKash payment receiving account.
                        </p>
                    </div>

                    <label class="flex cursor-pointer items-center gap-2">

                        <input
                            type="checkbox"
                            name="bkash_enabled"
                            value="1"
                            @checked(
                                old(
                                    'bkash_enabled',
                                    $setting->bkash_enabled
                                )
                            )
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        >

                        <span class="text-sm font-semibold text-slate-700">
                            Enabled
                        </span>

                    </label>

                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">

                    <div>

                        <label
                            for="bkash_number"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            bKash Number
                        </label>

                        <input
                            id="bkash_number"
                            type="text"
                            name="bkash_number"
                            value="{{ old(
                                'bkash_number',
                                $setting->bkash_number
                            ) }}"
                            placeholder="01XXXXXXXXX"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >

                    </div>

                    <div>

                        <label
                            for="bkash_account_type"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Account Type
                        </label>

                        <select
                            id="bkash_account_type"
                            name="bkash_account_type"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >
                            <option value="">Select</option>

                            @foreach([
                                'personal' => 'Personal',
                                'merchant' => 'Merchant',
                                'agent' => 'Agent',
                            ] as $value => $label)

                                <option
                                    value="{{ $value }}"
                                    @selected(
                                        old(
                                            'bkash_account_type',
                                            $setting->bkash_account_type
                                        ) === $value
                                    )
                                >
                                    {{ $label }}
                                </option>

                            @endforeach
                        </select>

                    </div>

                </div>

            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <h3 class="text-xl font-bold text-slate-900">
                            Nagad
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Configure Nagad payment receiving account.
                        </p>
                    </div>

                    <label class="flex cursor-pointer items-center gap-2">

                        <input
                            type="checkbox"
                            name="nagad_enabled"
                            value="1"
                            @checked(
                                old(
                                    'nagad_enabled',
                                    $setting->nagad_enabled
                                )
                            )
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        >

                        <span class="text-sm font-semibold text-slate-700">
                            Enabled
                        </span>

                    </label>

                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">

                    <div>

                        <label
                            for="nagad_number"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Nagad Number
                        </label>

                        <input
                            id="nagad_number"
                            type="text"
                            name="nagad_number"
                            value="{{ old(
                                'nagad_number',
                                $setting->nagad_number
                            ) }}"
                            placeholder="01XXXXXXXXX"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >

                    </div>

                    <div>

                        <label
                            for="nagad_account_type"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Account Type
                        </label>

                        <select
                            id="nagad_account_type"
                            name="nagad_account_type"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >
                            <option value="">Select</option>

                            @foreach([
                                'personal' => 'Personal',
                                'merchant' => 'Merchant',
                                'agent' => 'Agent',
                            ] as $value => $label)

                                <option
                                    value="{{ $value }}"
                                    @selected(
                                        old(
                                            'nagad_account_type',
                                            $setting->nagad_account_type
                                        ) === $value
                                    )
                                >
                                    {{ $label }}
                                </option>

                            @endforeach
                        </select>

                    </div>

                </div>

            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <label
                    for="payment_instruction"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Payment Instructions
                </label>

                <textarea
                    id="payment_instruction"
                    name="payment_instruction"
                    rows="6"
                    placeholder="Example: Send the exact subscription amount and enter the correct transaction ID."
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                >{{ old(
                    'payment_instruction',
                    $setting->payment_instruction
                ) }}</textarea>

            </section>

            <div class="flex justify-end">

                <button
                    type="submit"
                    class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Save Payment Settings
                </button>

            </div>

        </form>

    </div>

@endsection