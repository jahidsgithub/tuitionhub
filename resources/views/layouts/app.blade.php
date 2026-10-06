<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'Tuition Hub')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @stack('head')
</head>

<body class="min-h-screen bg-slate-50 text-slate-900">

    <div class="min-h-screen">

        <div
            data-sidebar-backdrop
            class="fixed inset-0 z-40 hidden bg-slate-950/60 opacity-0 backdrop-blur-sm transition-opacity duration-200 lg:hidden"
        ></div>

        <x-sidebar />

        <div class="min-h-screen lg:pl-72">

            <x-topbar
                :title="$pageTitle ?? null"
                :subtitle="$pageSubtitle ?? null"
            />

            <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">

                <div class="mx-auto max-w-[1600px]">

                    <x-flash />

                    @yield('content')

                </div>

            </main>

        </div>

    </div>

    @stack('scripts')

</body>
</html>