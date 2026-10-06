@props([
    'title' => null,
    'subtitle' => null,
])

@php
    $user = auth()->user();

    $unreadNotifications =
        $user
            ?->unreadNotifications()
            ->count() ?? 0;

    $roleLabel = match ($user?->role) {
        'admin' => 'Administrator',
        'teacher' => 'Teacher',
        'student' => 'Student / Guardian',
        default => 'Account',
    };
@endphp

<header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">

    <div class="flex h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">

        <div class="flex min-w-0 items-center gap-3">

            <button
                type="button"
                data-sidebar-open
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 lg:hidden"
                aria-label="Open navigation"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        stroke-linecap="round"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                </svg>
            </button>

            <div class="min-w-0">

                @if($subtitle)
                    <p class="truncate text-xs font-medium text-slate-500">
                        {{ $subtitle }}
                    </p>
                @endif

                <h1 class="truncate text-base font-bold text-slate-900 sm:text-lg">
                    {{ $title ?? 'Tuition Hub' }}
                </h1>

            </div>

        </div>

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">

            @if(Route::has('notifications.index'))

                <a
                    href="{{ route('notifications.index') }}"
                    class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-indigo-600"
                    title="Notifications"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022 23.848 23.848 0 0 0 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                        />
                    </svg>

                    @if($unreadNotifications > 0)

                        <span class="absolute -right-1 -top-1 flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">
                            {{
                                $unreadNotifications > 99
                                    ? '99+'
                                    : $unreadNotifications
                            }}
                        </span>

                    @endif

                </a>

            @endif

            <div class="relative">

                <button
                    type="button"
                    data-user-menu-button
                    class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-1.5 pr-2 shadow-sm transition hover:bg-slate-50"
                >
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-sm font-bold text-indigo-700">
                        {{
                            strtoupper(
                                substr(
                                    $user?->name ?? 'U',
                                    0,
                                    1
                                )
                            )
                        }}
                    </div>

                    <div class="hidden max-w-40 text-left sm:block">

                        <p class="truncate text-sm font-semibold text-slate-800">
                            {{ $user?->name }}
                        </p>

                        <p class="truncate text-[11px] text-slate-500">
                            {{ $roleLabel }}
                        </p>

                    </div>

                    <svg
                        class="hidden h-4 w-4 text-slate-400 sm:block"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m19.5 9-7.5 7.5L4.5 9"
                        />
                    </svg>
                </button>

                <div
                    data-user-menu
                    class="absolute right-0 mt-2 hidden w-60 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-xl"
                >
                    <div class="border-b border-slate-100 px-3 py-3">

                        <p class="truncate text-sm font-semibold">
                            {{ $user?->name }}
                        </p>

                        <p class="mt-1 truncate text-xs text-slate-500">
                            {{ $user?->email }}
                        </p>

                    </div>

                    @if(Route::has('account.settings.edit'))

                        <a
                            href="{{ route('account.settings.edit') }}"
                            class="mt-1 flex items-center rounded-xl px-3 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50"
                        >
                            Account Settings
                        </a>

                    @endif

                    @if(Route::has('account.security.edit'))

                        <a
                            href="{{ route('account.security.edit') }}"
                            class="flex items-center rounded-xl px-3 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50"
                        >
                            Security
                        </a>

                    @endif

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        class="mt-1 border-t border-slate-100 pt-1"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="flex w-full items-center rounded-xl px-3 py-2.5 text-left text-sm font-medium text-rose-600 transition hover:bg-rose-50"
                        >
                            Sign Out
                        </button>
                    </form>

                </div>

            </div>

        </div>

    </div>

</header>