@extends('layouts.app')

@section('title', 'Tuition Assignments - Tuition Hub Admin')

@php
    $pageTitle = 'Tuition Assignments';
    $pageSubtitle = 'Admin Panel';
@endphp

@section('content')

    <div>

        <h2 class="text-2xl font-black text-slate-900">
            Final Tuition Assignments
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Monitor all teacher-student tuition assignments across the platform.
        </p>

    </div>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm text-slate-500">
                Total Assignments
            </p>

            <p class="mt-2 text-3xl font-black text-slate-900">
                {{ number_format($totalAssignments) }}
            </p>

        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm text-slate-500">
                Active
            </p>

            <p class="mt-2 text-3xl font-black text-emerald-600">
                {{ number_format($activeAssignments) }}
            </p>

        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm text-slate-500">
                Completed
            </p>

            <p class="mt-2 text-3xl font-black text-indigo-600">
                {{ number_format($completedAssignments) }}
            </p>

        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm text-slate-500">
                Cancelled
            </p>

            <p class="mt-2 text-3xl font-black text-rose-600">
                {{ number_format($cancelledAssignments) }}
            </p>

        </div>

    </section>

    <section class="mt-4 grid gap-4 md:grid-cols-3">

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm text-slate-500">
                Via Teacher Application
            </p>

            <p class="mt-2 text-2xl font-black text-slate-900">
                {{ number_format($applicationAssignments) }}
            </p>

        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm text-slate-500">
                Via Direct Request
            </p>

            <p class="mt-2 text-2xl font-black text-slate-900">
                {{ number_format($directAssignments) }}
            </p>

        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-sm text-slate-500">
                Completion Rate
            </p>

            <p class="mt-2 text-2xl font-black text-indigo-600">
                {{ $completionRate }}%
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Completed vs completed + cancelled
            </p>

        </div>

    </section>

    <form
        method="GET"
        action="{{ route('admin.assignments.index') }}"
        class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >

        <div class="grid gap-4 md:grid-cols-4">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Tuition / teacher / student..."
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
                    value="active"
                    @selected(request('status') === 'active')
                >
                    Active
                </option>

                <option
                    value="completed"
                    @selected(request('status') === 'completed')
                >
                    Completed
                </option>

                <option
                    value="cancelled"
                    @selected(request('status') === 'cancelled')
                >
                    Cancelled
                </option>
            </select>

            <select
                name="source"
                class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
            >
                <option value="">
                    All Sources
                </option>

                <option
                    value="application"
                    @selected(request('source') === 'application')
                >
                    Teacher Application
                </option>

                <option
                    value="direct_request"
                    @selected(request('source') === 'direct_request')
                >
                    Direct Request
                </option>
            </select>

            <div class="flex gap-2">

                <button
                    type="submit"
                    class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Search / Filter
                </button>

                @if(
                    request()->filled('search') ||
                    request()->filled('status') ||
                    request()->filled('source')
                )

                    <a
                        href="{{ route('admin.assignments.index') }}"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600"
                    >
                        Clear
                    </a>

                @endif

            </div>

        </div>

    </form>

    <div class="mt-6 space-y-5">

        @forelse($assignments as $assignment)

            @php
                $statusClass = match($assignment->status) {
                    'active' => 'bg-emerald-50 text-emerald-700',
                    'completed' => 'bg-indigo-50 text-indigo-700',
                    default => 'bg-rose-50 text-rose-700',
                };
            @endphp

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="p-5 sm:p-6">

                    <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">

                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-2">

                                <h3 class="text-xl font-bold text-slate-900">
                                    {{
                                        $assignment
                                            ->tuitionPost
                                            ?->title
                                        ?? 'Unknown Tuition'
                                    }}
                                </h3>

                                <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $statusClass }}">
                                    {{ strtoupper($assignment->status) }}
                                </span>

                            </div>

                            <p class="mt-2 text-sm text-slate-500">
                                Tuition Code:
                                <strong class="text-slate-700">
                                    {{
                                        $assignment
                                            ->tuitionPost
                                            ?->tuition_code
                                        ?? 'N/A'
                                    }}
                                </strong>
                            </p>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

                                <div class="rounded-xl bg-slate-50 p-4">

                                    <p class="text-xs text-slate-400">
                                        Class
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-800">
                                        {{
                                            $assignment
                                                ->tuitionPost
                                                ?->class_level
                                            ?? 'N/A'
                                        }}
                                    </p>

                                </div>

                                <div class="rounded-xl bg-slate-50 p-4">

                                    <p class="text-xs text-slate-400">
                                        Salary
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-800">

                                        @if(
                                            $assignment
                                                ->tuitionPost
                                                ?->salary !== null
                                        )

                                            ৳{{ number_format(
                                                (float) $assignment
                                                    ->tuitionPost
                                                    ->salary
                                            ) }}

                                        @else

                                            N/A

                                        @endif

                                    </p>

                                </div>

                                <div class="rounded-xl bg-slate-50 p-4">

                                    <p class="text-xs text-slate-400">
                                        Teaching Mode
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-800">
                                        {{
                                            ucfirst(
                                                $assignment
                                                    ->tuitionPost
                                                    ?->teaching_mode
                                                ?? 'N/A'
                                            )
                                        }}
                                    </p>

                                </div>

                                <div class="rounded-xl bg-slate-50 p-4">

                                    <p class="text-xs text-slate-400">
                                        Source
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-800">
                                        {{
                                            $assignment->source === 'application'
                                                ? 'Teacher Application'
                                                : 'Direct Request'
                                        }}
                                    </p>

                                </div>

                            </div>

                        </div>

                        <div class="xl:w-64">

                            <div class="rounded-xl border border-slate-200 p-4">

                                <p class="text-xs text-slate-400">
                                    Assigned At
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{
                                        $assignment
                                            ->assigned_at
                                            ?->format('d M Y, h:i A')
                                        ?? 'N/A'
                                    }}
                                </p>

                                @if($assignment->completed_at)

                                    <div class="mt-4">

                                        <p class="text-xs text-slate-400">
                                            Completed At
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-slate-800">
                                            {{
                                                $assignment
                                                    ->completed_at
                                                    ->format('d M Y, h:i A')
                                            }}
                                        </p>

                                    </div>

                                @endif

                                @if($assignment->cancelled_at)

                                    <div class="mt-4">

                                        <p class="text-xs text-slate-400">
                                            Cancelled At
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-slate-800">
                                            {{
                                                $assignment
                                                    ->cancelled_at
                                                    ->format('d M Y, h:i A')
                                            }}
                                        </p>

                                    </div>

                                @endif

                            </div>

                        </div>

                    </div>

                    <div class="mt-6 grid gap-5 lg:grid-cols-2">

                        <section class="rounded-2xl border border-slate-200 p-5">

                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Student / Guardian
                            </p>

                            <h4 class="mt-2 text-lg font-bold text-slate-900">
                                {{
                                    $assignment
                                        ->student
                                        ?->name
                                    ?? 'N/A'
                                }}
                            </h4>

                            <div class="mt-3 space-y-1 text-sm text-slate-600">

                                <p>
                                    <strong>Email:</strong>
                                    {{
                                        $assignment
                                            ->student
                                            ?->email
                                        ?? 'N/A'
                                    }}
                                </p>

                                <p>
                                    <strong>Phone:</strong>
                                    {{
                                        $assignment
                                            ->student
                                            ?->phone
                                        ?? 'N/A'
                                    }}
                                </p>

                                @if(
                                    $assignment
                                        ->student
                                        ?->studentProfile
                                        ?->guardian_name
                                )

                                    <p>
                                        <strong>Guardian:</strong>
                                        {{
                                            $assignment
                                                ->student
                                                ->studentProfile
                                                ->guardian_name
                                        }}
                                    </p>

                                @endif

                            </div>

                        </section>

                        <section class="rounded-2xl border border-slate-200 p-5">

                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Assigned Teacher
                            </p>

                            <h4 class="mt-2 text-lg font-bold text-slate-900">
                                {{
                                    $assignment
                                        ->teacherProfile
                                        ?->user
                                        ?->name
                                    ?? 'N/A'
                                }}
                            </h4>

                            <div class="mt-3 space-y-1 text-sm text-slate-600">

                                <p>
                                    <strong>Email:</strong>
                                    {{
                                        $assignment
                                            ->teacherProfile
                                            ?->user
                                            ?->email
                                        ?? 'N/A'
                                    }}
                                </p>

                                <p>
                                    <strong>Phone:</strong>
                                    {{
                                        $assignment
                                            ->teacherProfile
                                            ?->user
                                            ?->phone
                                        ?? 'N/A'
                                    }}
                                </p>

                                <p>
                                    <strong>University:</strong>
                                    {{
                                        $assignment
                                            ->teacherProfile
                                            ?->university
                                        ?? 'N/A'
                                    }}
                                </p>

                                <p>
                                    <strong>Department:</strong>
                                    {{
                                        $assignment
                                            ->teacherProfile
                                            ?->department
                                        ?? 'N/A'
                                    }}
                                </p>

                            </div>

                        </section>

                    </div>

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

                <h3 class="text-xl font-bold text-slate-900">
                    No tuition assignments found
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Final teacher assignments will appear here.
                </p>

            </div>

        @endforelse

    </div>

    @if($assignments->hasPages())

        <div class="mt-8">
            {{ $assignments->links() }}
        </div>

    @endif

@endsection