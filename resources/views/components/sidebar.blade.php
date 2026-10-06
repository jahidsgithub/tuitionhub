@php
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();

    $role = $user?->role;

    $dashboardRoute = match ($role) {
        'admin' => 'admin.dashboard',
        'teacher' => 'teacher.dashboard',
        'student' => 'student.dashboard',
        default => null,
    };

    $navigation = match ($role) {
        'admin' => [
            [
                'label' => 'Dashboard',
                'route' => 'admin.dashboard',
                'match' => 'admin.dashboard',
                'icon' => 'home',
            ],
            [
                'label' => 'Users',
                'route' => 'admin.users.index',
                'match' => 'admin.users.*',
                'icon' => 'users',
            ],
            [
                'label' => 'Teacher Verification',
                'route' => 'admin.teachers.index',
                'match' => 'admin.teachers.*',
                'icon' => 'verified',
            ],
            [
                'label' => 'Verification Documents',
                'route' => 'admin.verification-documents.index',
                'match' => 'admin.verification-documents.*',
                'icon' => 'document',
            ],
            [
                'label' => 'Tuitions',
                'route' => 'admin.tuitions.index',
                'match' => 'admin.tuitions.*',
                'icon' => 'book',
            ],
            [
                'label' => 'Assignments',
                'route' => 'admin.assignments.index',
                'match' => 'admin.assignments.*',
                'icon' => 'assignment',
            ],
            [
                'label' => 'Payments',
                'route' => 'admin.payments.index',
                'match' => 'admin.payments.*',
                'icon' => 'payment',
            ],
            [
                'label' => 'Subscription Plans',
                'route' => 'admin.subscription-plans.index',
                'match' => 'admin.subscription-plans.*',
                'icon' => 'subscription',
            ],
            [
                'label' => 'Payment Settings',
                'route' => 'admin.payment-settings.edit',
                'match' => 'admin.payment-settings.*',
                'icon' => 'settings',
            ],
            [
                'label' => 'Complaints',
                'route' => 'admin.complaints.index',
                'match' => 'admin.complaints.*',
                'icon' => 'complaint',
            ],
            [
                'label' => 'Audit Logs',
                'route' => 'admin.audit-logs.index',
                'match' => 'admin.audit-logs.*',
                'icon' => 'history',
            ],
        ],

        'teacher' => [
            [
                'label' => 'Dashboard',
                'route' => 'teacher.dashboard',
                'match' => 'teacher.dashboard',
                'icon' => 'home',
            ],
            [
                'label' => 'Find Tuition',
                'route' => 'teacher.tuitions.index',
                'match' => 'teacher.tuitions.index',
                'icon' => 'search',
            ],
            [
                'label' => 'My Applications',
                'route' => 'teacher.tuitions.applications',
                'match' => 'teacher.tuitions.applications',
                'icon' => 'document',
            ],
            [
                'label' => 'Incoming Requests',
                'route' => 'teacher.requests.index',
                'match' => 'teacher.requests.*',
                'icon' => 'inbox',
            ],
            [
                'label' => 'Assignments',
                'route' => 'teacher.assignments.index',
                'match' => 'teacher.assignments.*',
                'icon' => 'assignment',
            ],
            [
                'label' => 'Subscription',
                'route' => 'teacher.subscription.index',
                'match' => 'teacher.subscription.*',
                'icon' => 'subscription',
            ],
            [
                'label' => 'Verification Documents',
                'route' => 'teacher.verification-documents.index',
                'match' => 'teacher.verification-documents.*',
                'icon' => 'verified',
            ],
            [
                'label' => 'Complaints',
                'route' => 'teacher.complaints.index',
                'match' => 'teacher.complaints.*',
                'icon' => 'complaint',
            ],
            [
                'label' => 'Profile',
                'route' => 'teacher.profile.edit',
                'match' => 'teacher.profile.*',
                'icon' => 'profile',
            ],
        ],

        'student' => [
            [
                'label' => 'Dashboard',
                'route' => 'student.dashboard',
                'match' => 'student.dashboard',
                'icon' => 'home',
            ],
            [
                'label' => 'Find Teachers',
                'route' => 'student.teachers.index',
                'match' => 'student.teachers.*',
                'icon' => 'search',
            ],
            [
                'label' => 'Teacher Requests',
                'route' => 'student.teacher-requests.index',
                'match' => 'student.teacher-requests.*',
                'icon' => 'inbox',
            ],
            [
                'label' => 'My Tuitions',
                'route' => 'student.tuitions.index',
                'match' => 'student.tuitions.*',
                'icon' => 'book',
            ],
            [
                'label' => 'Assignments',
                'route' => 'student.assignments.index',
                'match' => 'student.assignments.*',
                'icon' => 'assignment',
            ],
            [
                'label' => 'Complaints',
                'route' => 'student.complaints.index',
                'match' => 'student.complaints.*',
                'icon' => 'complaint',
            ],
            [
                'label' => 'Profile',
                'route' => 'student.profile.edit',
                'match' => 'student.profile.*',
                'icon' => 'profile',
            ],
        ],

        default => [],
    };

    $roleLabel = match ($role) {
        'admin' => 'Administrator',
        'teacher' => 'Teacher',
        'student' => 'Student / Guardian',
        default => 'Account',
    };

    $iconPaths = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 11.204 3.046a1.125 1.125 0 0 1 1.592 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-6.75h4.5V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>',

        'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72M18 18.72v-.94c0-.793-.134-1.555-.38-2.265M18 18.72A9.1 9.1 0 0 1 12 21c-2.305 0-4.41-.857-6-2.27m12 0a9.092 9.092 0 0 0-.38-3.215m0 0a5.381 5.381 0 0 0-10.24 0M6 18.72v-.94c0-.793.134-1.555.38-2.265m0 0a3 3 0 0 0-4.681 2.72A9.094 9.094 0 0 0 6 18.72M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>',

        'verified' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m6-3.375c0 1.035-.84 1.875-1.875 1.875-.621 0-1.125.504-1.125 1.125 0 1.035-.84 1.875-1.875 1.875-.621 0-1.125.504-1.125 1.125 0 1.035-.84 1.875-1.875 1.875-.621 0-1.125.504-1.125 1.125 0 1.035-.84 1.875-1.875 1.875A1.875 1.875 0 0 1 8.25 15.375c0-.621-.504-1.125-1.125-1.125A1.875 1.875 0 0 1 5.25 12.375c0-.621-.504-1.125-1.125-1.125A1.875 1.875 0 0 1 2.25 9.375C2.25 8.34 3.09 7.5 4.125 7.5c.621 0 1.125-.504 1.125-1.125A1.875 1.875 0 0 1 7.125 4.5c.621 0 1.125-.504 1.125-1.125A1.875 1.875 0 0 1 10.125 1.5c1.035 0 1.875.84 1.875 1.875 0 .621.504 1.125 1.125 1.125A1.875 1.875 0 0 1 15 6.375c0 .621.504 1.125 1.125 1.125A1.875 1.875 0 0 1 18 9.375c0 .621.504 1.125 1.125 1.125C20.16 10.5 21 9.66 21 8.625"/>',

        'document' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h4.5m1.5-15.75H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V7.5L14.25 2.25Z"/>',

        'book' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"/>',

        'assignment' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5.25H6.75A2.25 2.25 0 0 0 4.5 7.5v12a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-12a2.25 2.25 0 0 0-2.25-2.25H15M9 5.25a3 3 0 0 1 6 0M9 5.25A3 3 0 0 0 12 8.25a3 3 0 0 0 3-3m-6 6h6m-6 3h4.5"/>',

        'payment' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M3.75 5.25h16.5A1.5 1.5 0 0 1 21.75 6.75v10.5a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5V6.75a1.5 1.5 0 0 1 1.5-1.5Zm2.25 9h3"/>',

        'subscription' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111 5.518.442c.499.04.701.663.321.988l-4.204 3.602 1.285 5.385a.562.562 0 0 1-.84.61L12 16.739l-4.725 2.898a.562.562 0 0 1-.84-.61l1.285-5.385-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442L11.48 3.5Z"/>',

        'settings' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.281a1.125 1.125 0 0 0 1.688.782l1.151-.665a1.125 1.125 0 0 1 1.536.412l1.296 2.245a1.125 1.125 0 0 1-.412 1.536l-1.15.664a1.125 1.125 0 0 0 0 1.95l1.15.664c.538.31.723.999.412 1.536l-1.296 2.245a1.125 1.125 0 0 1-1.536.412l-1.151-.665a1.125 1.125 0 0 0-1.688.782l-.213 1.281c-.09.542-.56.94-1.11.94h-2.592c-.55 0-1.02-.398-1.11-.94l-.213-1.281a1.125 1.125 0 0 0-1.688-.782l-1.151.665a1.125 1.125 0 0 1-1.536-.412L3.66 16.005a1.125 1.125 0 0 1 .412-1.536l1.15-.664a1.125 1.125 0 0 0 0-1.95l-1.15-.664a1.125 1.125 0 0 1-.412-1.536L4.956 7.41a1.125 1.125 0 0 1 1.536-.412l1.151.665a1.125 1.125 0 0 0 1.688-.782l.213-1.281Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>',

        'complaint' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-1.5c0 5.385-4.365 9.75-9.75 9.75S1.5 16.635 1.5 11.25 5.865 1.5 11.25 1.5 21 5.865 21 11.25ZM12 16.5h.008v.008H12V16.5Z"/>',

        'history' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v4.5l3 1.5m6-3a9 9 0 1 1-3.219-6.905M21 3v6h-6"/>',

        'search' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z"/>',

        'inbox' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75 5.25 6h13.5l3 6.75v5.25A2.25 2.25 0 0 1 19.5 20.25h-15A2.25 2.25 0 0 1 2.25 18v-5.25Zm0 0H7.5l1.5 2.25h6l1.5-2.25h5.25"/>',

        'profile' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 21a7.5 7.5 0 0 1 15 0"/>',
    ];
@endphp

<aside
    data-app-sidebar
    class="app-scrollbar fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col overflow-y-auto bg-slate-950 text-white shadow-2xl transition-transform duration-200 lg:translate-x-0"
>
    <div class="flex h-20 shrink-0 items-center justify-between border-b border-slate-800 px-5">

        @if(
            $dashboardRoute &&
            Route::has($dashboardRoute)
        )
            <a
                href="{{ route($dashboardRoute) }}"
                class="flex min-w-0 items-center gap-3"
            >
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-base font-black shadow-lg shadow-indigo-950/40">
                    TH
                </div>

                <div class="min-w-0">
                    <p class="truncate text-base font-bold">
                        Tuition Hub
                    </p>

                    <p class="mt-0.5 truncate text-xs text-slate-400">
                        {{ $roleLabel }}
                    </p>
                </div>
            </a>
        @endif

        <button
            type="button"
            data-sidebar-close
            class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-900 hover:text-white lg:hidden"
            aria-label="Close navigation"
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
                    d="M6 18 18 6M6 6l12 12"
                />
            </svg>
        </button>

    </div>

    <div class="flex-1 px-4 py-6">

        <p class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">
            Navigation
        </p>

        <nav class="space-y-1">

            @foreach($navigation as $item)

                @if(Route::has($item['route']))

                    @php
                        $active = request()->routeIs(
                            $item['match']
                        );
                    @endphp

                    <a
                        href="{{ route($item['route']) }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition
                            {{
                                $active
                                    ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-950/25'
                                    : 'text-slate-300 hover:bg-slate-900 hover:text-white'
                            }}"
                    >
                        <svg
                            class="h-5 w-5 shrink-0 {{
                                $active
                                    ? 'text-white'
                                    : 'text-slate-500 group-hover:text-slate-300'
                            }}"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                        >
                            {!! $iconPaths[$item['icon']] ?? '' !!}
                        </svg>

                        <span class="truncate">
                            {{ $item['label'] }}
                        </span>
                    </a>

                @endif

            @endforeach

            @if(Route::has('notifications.index'))

                <a
                    href="{{ route('notifications.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition
                        {{
                            request()->routeIs('notifications.*')
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-950/25'
                                : 'text-slate-300 hover:bg-slate-900 hover:text-white'
                        }}"
                >
                    <svg
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022 23.848 23.848 0 0 0 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                        />
                    </svg>

                    <span>
                        Notifications
                    </span>

                    @php
                        $sidebarUnread =
                            $user
                                ?->unreadNotifications()
                                ->count() ?? 0;
                    @endphp

                    @if($sidebarUnread > 0)
                        <span class="ml-auto inline-flex min-w-6 items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[11px] font-bold text-white">
                            {{
                                $sidebarUnread > 99
                                    ? '99+'
                                    : $sidebarUnread
                            }}
                        </span>
                    @endif
                </a>

            @endif

        </nav>

    </div>

    <div class="border-t border-slate-800 p-4">

        <div class="rounded-2xl bg-slate-900 p-4">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-500/20 font-bold text-indigo-300">
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

                <div class="min-w-0 flex-1">

                    <p class="truncate text-sm font-semibold">
                        {{ $user?->name }}
                    </p>

                    <p class="mt-0.5 truncate text-xs text-slate-500">
                        {{ $user?->email }}
                    </p>

                </div>

            </div>

            <form
                method="POST"
                action="{{ route('logout') }}"
                class="mt-4"
            >
                @csrf

                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-700 px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
                >
                    Sign Out
                </button>
            </form>

        </div>

    </div>
</aside>