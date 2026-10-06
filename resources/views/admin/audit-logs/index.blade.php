@extends('layouts.app')

@section('title', 'Audit Logs - Tuition Hub Admin')

@php
    $pageTitle = 'Admin Audit Logs';
    $pageSubtitle = 'Admin Panel';
@endphp

@section('content')

    <div>
        <h2 class="text-2xl font-black text-slate-900">
            Admin Audit Logs
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Permanent history of administrative changes across Tuition Hub.
        </p>
    </div>

    <section class="mt-6 grid gap-4 sm:grid-cols-3">

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm text-slate-500">
                Total Actions
            </p>

            <p class="mt-2 text-3xl font-black text-slate-900">
                {{ number_format($totalLogs) }}
            </p>

        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm text-slate-500">
                Actions Today
            </p>

            <p class="mt-2 text-3xl font-black text-indigo-600">
                {{ number_format($todayLogs) }}
            </p>

        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm text-slate-500">
                Active Admins — 7 Days
            </p>

            <p class="mt-2 text-3xl font-black text-emerald-600">
                {{ number_format($recentAdminCount) }}
            </p>

        </div>

    </section>

    <form
        method="GET"
        action="{{ route('admin.audit-logs.index') }}"
        class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >

        <div class="grid gap-4 md:grid-cols-5">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Action / route / target..."
                class="rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >

            <select
                name="admin"
                class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >
                <option value="">All Admins</option>

                @foreach($admins as $admin)

                    <option
                        value="{{ $admin->id }}"
                        @selected(
                            (string) request('admin') ===
                            (string) $admin->id
                        )
                    >
                        {{ $admin->name }}
                    </option>

                @endforeach
            </select>

            <select
                name="action"
                class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >
                <option value="">All Actions</option>

                @foreach($actions as $action)

                    <option
                        value="{{ $action }}"
                        @selected(request('action') === $action)
                    >
                        {{ $action }}
                    </option>

                @endforeach
            </select>

            <select
                name="method"
                class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >
                <option value="">All Methods</option>

                @foreach([
                    'POST',
                    'PUT',
                    'PATCH',
                    'DELETE',
                ] as $method)

                    <option
                        value="{{ $method }}"
                        @selected(request('method') === $method)
                    >
                        {{ $method }}
                    </option>

                @endforeach
            </select>

            <button
                type="submit"
                class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
            >
                Filter
            </button>

        </div>

        @if(
            request()->filled('search') ||
            request()->filled('admin') ||
            request()->filled('action') ||
            request()->filled('method')
        )

            <div class="mt-4">

                <a
                    href="{{ route('admin.audit-logs.index') }}"
                    class="text-sm font-semibold text-indigo-600"
                >
                    Clear Filters
                </a>

            </div>

        @endif

    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-slate-50">

                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">

                        <th class="px-5 py-4">
                            Date
                        </th>

                        <th class="px-5 py-4">
                            Admin
                        </th>

                        <th class="px-5 py-4">
                            Action
                        </th>

                        <th class="px-5 py-4">
                            Target
                        </th>

                        <th class="px-5 py-4">
                            Method
                        </th>

                        <th class="px-5 py-4">
                            Details
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($logs as $log)

                        @php
                            $methodClass = match($log->method) {
                                'DELETE' => 'bg-rose-50 text-rose-700',
                                'POST' => 'bg-emerald-50 text-emerald-700',
                                'PUT', 'PATCH' => 'bg-indigo-50 text-indigo-700',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp

                        <tr class="align-top">

                            <td class="whitespace-nowrap px-5 py-4">

                                <p class="font-semibold text-slate-800">
                                    {{
                                        $log
                                            ->created_at
                                            ?->format('d M Y')
                                    }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{
                                        $log
                                            ->created_at
                                            ?->format('h:i:s A')
                                    }}
                                </p>

                            </td>

                            <td class="px-5 py-4">

                                <p class="font-semibold text-slate-800">
                                    {{ $log->actor?->name ?? 'Deleted Admin' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $log->actor?->email ?? '' }}
                                </p>

                            </td>

                            <td class="px-5 py-4">

                                <p class="font-semibold text-slate-800">
                                    {{ $log->action }}
                                </p>

                                @if($log->route_name)

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $log->route_name }}
                                    </p>

                                @endif

                            </td>

                            <td class="px-5 py-4">

                                @if($log->target_type)

                                    <p class="font-semibold text-slate-800">
                                        {{ $log->target_type }}
                                    </p>

                                    @if($log->target_id)

                                        <p class="mt-1 text-xs text-slate-500">
                                            ID: {{ $log->target_id }}
                                        </p>

                                    @endif

                                @else

                                    <span class="text-slate-400">
                                        —
                                    </span>

                                @endif

                            </td>

                            <td class="px-5 py-4">

                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $methodClass }}">
                                    {{ $log->method }}
                                </span>

                            </td>

                            <td class="px-5 py-4">

                                @if(
                                    is_array($log->metadata) &&
                                    count($log->metadata)
                                )

                                    <div class="space-y-1">

                                        @foreach(
                                            $log->metadata
                                            as $key => $value
                                        )

                                            <p class="text-sm">

                                                <span class="text-slate-500">
                                                    {{
                                                        ucfirst(
                                                            str_replace(
                                                                '_',
                                                                ' ',
                                                                $key
                                                            )
                                                        )
                                                    }}:
                                                </span>

                                                <span class="font-medium text-slate-800">

                                                    @if(is_bool($value))

                                                        {{ $value ? 'Yes' : 'No' }}

                                                    @elseif($value === null)

                                                        N/A

                                                    @elseif(is_array($value) || is_object($value))

                                                        {{ json_encode(
                                                            $value,
                                                            JSON_UNESCAPED_UNICODE
                                                        ) }}

                                                    @else

                                                        {{ $value }}

                                                    @endif

                                                </span>

                                            </p>

                                        @endforeach

                                    </div>

                                @else

                                    <span class="text-slate-400">
                                        —
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="px-6 py-14 text-center text-slate-500"
                            >
                                No audit logs found.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    @if($logs->hasPages())
        <div class="mt-8">
            {{ $logs->links() }}
        </div>
    @endif

@endsection