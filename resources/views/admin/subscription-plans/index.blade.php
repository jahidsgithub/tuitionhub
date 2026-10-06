@extends('layouts.app')

@section('title', 'Subscription Plans - Tuition Hub Admin')

@php
    $pageTitle = 'Subscription Plans';
    $pageSubtitle = 'Admin Panel';
@endphp

@section('content')

    <div>
        <h2 class="text-2xl font-black text-slate-900">
            Subscription Plans
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Manage pricing, duration and application limits.
        </p>
    </div>

    <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">

        @forelse($plans as $plan)

            <article class="relative rounded-2xl border bg-white p-6 shadow-sm {{ $plan->is_featured ? 'border-indigo-400' : 'border-slate-200' }}">

                <div class="flex items-start justify-between gap-3">

                    <div>
                        <h3 class="text-2xl font-black text-slate-900">
                            {{ $plan->name }}
                        </h3>

                        <p class="mt-1 text-xs text-slate-400">
                            {{ $plan->slug }}
                        </p>
                    </div>

                    @if($plan->status)

                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold text-emerald-700">
                            ACTIVE
                        </span>

                    @else

                        <span class="rounded-full bg-rose-50 px-3 py-1 text-[10px] font-bold text-rose-700">
                            INACTIVE
                        </span>

                    @endif

                </div>

                @if($plan->is_featured)

                    <span class="mt-3 inline-flex rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-bold text-indigo-700">
                        FEATURED
                    </span>

                @endif

                <p class="mt-4 min-h-[40px] text-sm leading-6 text-slate-500">
                    {{ $plan->description ?? 'No description.' }}
                </p>

                <div class="mt-6">

                    @if((float) $plan->price === 0.0)

                        <span class="text-4xl font-black text-slate-900">
                            Free
                        </span>

                    @else

                        <span class="text-4xl font-black text-slate-900">
                            ৳{{ number_format((float) $plan->price) }}
                        </span>

                    @endif

                </div>

                <div class="mt-6 space-y-3 text-sm">

                    <div class="flex justify-between gap-3">
                        <span class="text-slate-500">Duration</span>

                        <strong class="text-slate-800">
                            {{ $plan->duration_days }} Days
                        </strong>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="text-slate-500">
                            Applications
                        </span>

                        <strong class="text-slate-800">

                            @if($plan->application_limit === null)
                                Unlimited
                            @else
                                {{ $plan->application_limit }}
                            @endif

                        </strong>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="text-slate-500">
                            Sort Order
                        </span>

                        <strong class="text-slate-800">
                            {{ $plan->sort_order }}
                        </strong>
                    </div>

                </div>

                <div class="mt-8 grid grid-cols-2 gap-3">

                    <a
                        href="{{ route(
                            'admin.subscription-plans.edit',
                            $plan
                        ) }}"
                        class="rounded-xl bg-indigo-600 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-indigo-700"
                    >
                        Edit
                    </a>

                    <form
                        method="POST"
                        action="{{ route(
                            'admin.subscription-plans.toggle',
                            $plan
                        ) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            onclick="return confirm('{{ $plan->status ? 'Disable' : 'Enable' }} this plan?')"
                            class="w-full rounded-xl border px-4 py-3 text-sm font-semibold transition
                                {{ $plan->status
                                    ? 'border-rose-200 text-rose-600 hover:bg-rose-50'
                                    : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50'
                                }}"
                        >
                            {{ $plan->status ? 'Disable' : 'Enable' }}
                        </button>

                    </form>

                </div>

            </article>

        @empty

            <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center text-sm text-slate-500">
                No subscription plans found.
            </div>

        @endforelse

    </div>

@endsection