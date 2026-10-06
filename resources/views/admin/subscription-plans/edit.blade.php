@extends('layouts.app')

@section('title', 'Edit Plan - Tuition Hub Admin')

@php
    $pageTitle = 'Edit Subscription Plan';
    $pageSubtitle = $plan->name;
@endphp

@section('content')

    <div class="mx-auto max-w-3xl">

        <a
            href="{{ route('admin.subscription-plans.index') }}"
            class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
        >
            ← Back to Subscription Plans
        </a>

        <div class="mt-5">
            <h2 class="text-2xl font-black text-slate-900">
                Edit Subscription Plan
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                {{ $plan->name }} — {{ $plan->slug }}
            </p>
        </div>

        <form
            method="POST"
            action="{{ route(
                'admin.subscription-plans.update',
                $plan
            ) }}"
            class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
        >
            @csrf
            @method('PUT')

            <div>

                <label
                    for="name"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Plan Name
                </label>

                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name', $plan->name) }}"
                    required
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
                    rows="4"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                >{{ old('description', $plan->description) }}</textarea>

            </div>

            <div class="mt-5 grid gap-5 md:grid-cols-2">

                <div>

                    <label
                        for="price"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Price (BDT)
                    </label>

                    <input
                        id="price"
                        type="number"
                        step="0.01"
                        min="0"
                        name="price"
                        value="{{ old('price', $plan->price) }}"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                </div>

                <div>

                    <label
                        for="duration_days"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Duration (Days)
                    </label>

                    <input
                        id="duration_days"
                        type="number"
                        min="1"
                        name="duration_days"
                        value="{{ old(
                            'duration_days',
                            $plan->duration_days
                        ) }}"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                </div>

                <div>

                    <label
                        for="application_limit"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Application Limit
                    </label>

                    <input
                        id="application_limit"
                        type="number"
                        min="1"
                        name="application_limit"
                        value="{{ old(
                            'application_limit',
                            $plan->application_limit
                        ) }}"
                        placeholder="Leave blank for unlimited"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                    <p class="mt-2 text-xs text-slate-500">
                        Leave blank for unlimited applications.
                    </p>

                </div>

                <div>

                    <label
                        for="sort_order"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Sort Order
                    </label>

                    <input
                        id="sort_order"
                        type="number"
                        min="0"
                        name="sort_order"
                        value="{{ old(
                            'sort_order',
                            $plan->sort_order
                        ) }}"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                </div>

            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">

                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 p-4">

                    <input
                        type="checkbox"
                        name="is_featured"
                        value="1"
                        @checked(
                            old(
                                'is_featured',
                                $plan->is_featured
                            )
                        )
                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    >

                    <span>
                        <strong class="block text-slate-800">
                            Featured Plan
                        </strong>

                        <span class="mt-1 block text-xs text-slate-500">
                            Highlight this plan to teachers.
                        </span>
                    </span>

                </label>

                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 p-4">

                    <input
                        type="checkbox"
                        name="status"
                        value="1"
                        @checked(
                            old(
                                'status',
                                $plan->status
                            )
                        )
                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    >

                    <span>
                        <strong class="block text-slate-800">
                            Active Plan
                        </strong>

                        <span class="mt-1 block text-xs text-slate-500">
                            Allow teachers to select this plan.
                        </span>
                    </span>

                </label>

            </div>

            <div class="mt-8 flex justify-end gap-3">

                <a
                    href="{{ route('admin.subscription-plans.index') }}"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

@endsection