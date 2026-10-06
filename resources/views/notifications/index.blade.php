@extends('layouts.app')

@section('title', 'Notifications - Tuition Hub')

@php
    $pageTitle = 'Notifications';
    $pageSubtitle = 'Account Activity';
@endphp

@section('content')

    <div class="mx-auto max-w-5xl">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h2 class="text-2xl font-black text-slate-900">
                    Notifications
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $unreadCount }} unread notification(s)
                </p>

            </div>

            @if($unreadCount > 0)

                <form
                    method="POST"
                    action="{{ route('notifications.read-all') }}"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700"
                    >
                        Mark All as Read
                    </button>

                </form>

            @endif

        </div>

        <div class="mt-6 space-y-4">

            @forelse($notifications as $notification)

                @php
                    $isUnread = ! $notification->read_at;
                    $notificationUrl = data_get(
                        $notification->data,
                        'url'
                    );
                @endphp

                <article
                    class="rounded-2xl border p-5 shadow-sm transition sm:p-6
                        {{
                            $isUnread
                                ? 'border-indigo-200 bg-indigo-50/60'
                                : 'border-slate-200 bg-white'
                        }}"
                >

                    <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">

                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-2">

                                <h3 class="text-lg font-bold text-slate-900">
                                    {{
                                        data_get(
                                            $notification->data,
                                            'title',
                                            'Notification'
                                        )
                                    }}
                                </h3>

                                @if($isUnread)

                                    <span class="rounded-full bg-indigo-600 px-2.5 py-1 text-[10px] font-bold text-white">
                                        NEW
                                    </span>

                                @endif

                            </div>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                {{
                                    data_get(
                                        $notification->data,
                                        'message',
                                        'You have a new notification.'
                                    )
                                }}
                            </p>

                            <p class="mt-3 text-xs text-slate-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>

                        </div>

                        <div class="flex shrink-0 flex-col gap-2 md:w-40">

                            @if($isUnread)

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'notifications.read',
                                        $notification->id
                                    ) }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700"
                                    >
                                        View
                                    </button>

                                </form>

                            @elseif($notificationUrl)

                                <a
                                    href="{{ $notificationUrl }}"
                                    class="block w-full rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-center text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50"
                                >
                                    Open
                                </a>

                            @endif

                            <form
                                method="POST"
                                action="{{ route(
                                    'notifications.destroy',
                                    $notification->id
                                ) }}"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    onclick="return confirm('Delete this notification?')"
                                    class="w-full rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                                >
                                    Delete
                                </button>

                            </form>

                        </div>

                    </div>

                </article>

            @empty

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">

                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl">
                        🔔
                    </div>

                    <h3 class="mt-4 text-lg font-bold text-slate-900">
                        No notifications yet
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Your Tuition Hub activity notifications will appear here.
                    </p>

                </div>

            @endforelse

        </div>

        @if($notifications->hasPages())

            <div class="mt-8">
                {{ $notifications->links() }}
            </div>

        @endif

    </div>

@endsection