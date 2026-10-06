<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="description"
        content="Tuition Hub connects students and guardians with verified teachers."
    >

    <title>Tuition Hub - Find the Right Teacher</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

</head>

<body class="min-h-screen bg-slate-50 text-slate-900">

    <header class="border-b border-slate-200 bg-white/90 backdrop-blur">

        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-6 lg:px-8">

            <a
                href="{{ url('/') }}"
                class="flex items-center gap-3"
            >

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-lg font-black text-white">
                    T
                </span>

                <span>

                    <strong class="block text-lg font-black text-slate-900">
                        Tuition Hub
                    </strong>

                    <span class="block text-xs text-slate-500">
                        Teacher Marketplace
                    </span>

                </span>

            </a>

            <nav class="flex items-center gap-2">

                @auth

                    <a
                        href="{{ route('dashboard') }}"
                        class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700"
                    >
                        Dashboard
                    </a>

                @else

                    @if(Route::has('login'))

                        <a
                            href="{{ route('login') }}"
                            class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                        >
                            Login
                        </a>

                    @endif

                    @if(Route::has('register'))

                        <a
                            href="{{ route('register') }}"
                            class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700"
                        >
                            Create Account
                        </a>

                    @endif

                @endauth

            </nav>

        </div>

    </header>

    <main>

        <section class="relative overflow-hidden">

            <div class="absolute inset-0 -z-10 bg-gradient-to-br from-indigo-50 via-white to-violet-50"></div>

            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-16 sm:px-6 sm:py-20 lg:grid-cols-2 lg:items-center lg:px-8 lg:py-28">

                <div>

                    <span class="inline-flex rounded-full border border-indigo-200 bg-indigo-50 px-4 py-2 text-xs font-bold text-indigo-700">
                        Students • Guardians • Verified Teachers
                    </span>

                    <h1 class="mt-6 max-w-3xl text-4xl font-black tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                        Find the right teacher for your learning goals.
                    </h1>

                    <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                        Tuition Hub makes it easier for students and guardians
                        to post tuition requirements, discover verified teachers
                        and manage tuition assignments in one place.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">

                        @auth

                            <a
                                href="{{ route('dashboard') }}"
                                class="inline-flex justify-center rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-100 transition hover:bg-indigo-700"
                            >
                                Go to Dashboard
                            </a>

                        @else

                            @if(Route::has('register'))

                                <a
                                    href="{{ route('register') }}"
                                    class="inline-flex justify-center rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-100 transition hover:bg-indigo-700"
                                >
                                    Get Started
                                </a>

                            @endif

                            @if(Route::has('login'))

                                <a
                                    href="{{ route('login') }}"
                                    class="inline-flex justify-center rounded-xl border border-slate-300 bg-white px-6 py-3.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                >
                                    Login
                                </a>

                            @endif

                        @endauth

                    </div>

                </div>

                <div class="relative">

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60 sm:p-8">

                        <div class="grid gap-4 sm:grid-cols-2">

                            <div class="rounded-2xl bg-indigo-50 p-5">

                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-600 text-xl text-white">
                                    🎓
                                </div>

                                <h3 class="mt-4 font-bold text-slate-900">
                                    Verified Teachers
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    Discover teachers with reviewed profiles and verification status.
                                </p>

                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-5">

                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-600 text-xl text-white">
                                    📚
                                </div>

                                <h3 class="mt-4 font-bold text-slate-900">
                                    Tuition Marketplace
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    Students post requirements and teachers can apply to suitable opportunities.
                                </p>

                            </div>

                            <div class="rounded-2xl bg-violet-50 p-5">

                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-600 text-xl text-white">
                                    🤝
                                </div>

                                <h3 class="mt-4 font-bold text-slate-900">
                                    Direct Requests
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    Students can find a teacher and send a direct tuition request.
                                </p>

                            </div>

                            <div class="rounded-2xl bg-amber-50 p-5">

                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500 text-xl text-white">
                                    🔒
                                </div>

                                <h3 class="mt-4 font-bold text-slate-900">
                                    Controlled Contact
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    Contact information is revealed through the assignment workflow.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

        <section class="border-y border-slate-200 bg-white">

            <div class="mx-auto max-w-7xl px-5 py-16 sm:px-6 lg:px-8">

                <div class="text-center">

                    <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">
                        How It Works
                    </p>

                    <h2 class="mt-3 text-3xl font-black text-slate-900">
                        A simple tuition matching process
                    </h2>

                </div>

                <div class="mt-10 grid gap-5 md:grid-cols-3">

                    <div class="rounded-2xl border border-slate-200 p-6">

                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-sm font-black text-white">
                            1
                        </span>

                        <h3 class="mt-5 text-lg font-bold text-slate-900">
                            Create your profile
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Register as a student or teacher and complete the information needed for the marketplace.
                        </p>

                    </div>

                    <div class="rounded-2xl border border-slate-200 p-6">

                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-sm font-black text-white">
                            2
                        </span>

                        <h3 class="mt-5 text-lg font-bold text-slate-900">
                            Find the right match
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Post a tuition, browse opportunities, find teachers or send direct requests.
                        </p>

                    </div>

                    <div class="rounded-2xl border border-slate-200 p-6">

                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-sm font-black text-white">
                            3
                        </span>

                        <h3 class="mt-5 text-lg font-bold text-slate-900">
                            Confirm the tuition
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Finalize the teacher assignment and manage the tuition from your dashboard.
                        </p>

                    </div>

                </div>

            </div>

        </section>

        <section class="bg-slate-950">

            <div class="mx-auto max-w-7xl px-5 py-16 text-center sm:px-6 lg:px-8">

                <h2 class="text-3xl font-black text-white">
                    Ready to use Tuition Hub?
                </h2>

                <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-300">
                    Create an account and start finding teachers or tuition opportunities.
                </p>

                <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">

                    @auth

                        <a
                            href="{{ route('dashboard') }}"
                            class="rounded-xl bg-white px-6 py-3 text-sm font-bold text-slate-900 transition hover:bg-slate-100"
                        >
                            Open Dashboard
                        </a>

                    @else

                        @if(Route::has('register'))

                            <a
                                href="{{ route('register') }}"
                                class="rounded-xl bg-indigo-500 px-6 py-3 text-sm font-bold text-white transition hover:bg-indigo-400"
                            >
                                Create Account
                            </a>

                        @endif

                        @if(Route::has('login'))

                            <a
                                href="{{ route('login') }}"
                                class="rounded-xl border border-slate-700 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-900"
                            >
                                Login
                            </a>

                        @endif

                    @endauth

                </div>

            </div>

        </section>

    </main>

    <footer class="border-t border-slate-200 bg-white">

        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">

            <p>
                © {{ now()->year }} Tuition Hub
            </p>

            <p>
                Tuition marketplace for students, guardians and teachers.
            </p>

        </div>

    </footer>

</body>

</html>