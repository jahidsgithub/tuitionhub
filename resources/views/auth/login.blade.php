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

    <title>Login - Tuition Hub</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="min-h-screen bg-slate-950">

<div class="min-h-screen lg:grid lg:grid-cols-2">

    <section class="relative hidden overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-700 p-12 text-white lg:flex lg:flex-col lg:justify-between">

        <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-violet-300/10 blur-3xl"></div>

        <div class="relative z-10">

            <a
                href="{{ route('home') }}"
                class="inline-flex items-center gap-3"
            >
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white font-black text-indigo-700 shadow-lg">
                    TH
                </div>

                <div>
                    <p class="text-xl font-black">
                        Tuition Hub
                    </p>

                    <p class="text-xs text-indigo-100">
                        Smart Tuition Marketplace
                    </p>
                </div>
            </a>

        </div>

        <div class="relative z-10 max-w-xl">

            <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-indigo-100">
                Welcome Back
            </span>

            <h1 class="mt-5 text-4xl font-black leading-tight xl:text-5xl">
                Learn, teach and connect from one trusted platform.
            </h1>

            <p class="mt-5 max-w-lg text-base leading-7 text-indigo-100">
                Students can find verified teachers and teachers can discover tuition opportunities through a secure, organized marketplace.
            </p>

            <div class="mt-8 grid gap-3 sm:grid-cols-2">

                <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                    <p class="font-bold">
                        Verified Teachers
                    </p>

                    <p class="mt-1 text-sm text-indigo-100">
                        Search trusted and available teacher profiles.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                    <p class="font-bold">
                        Easy Tuition Matching
                    </p>

                    <p class="mt-1 text-sm text-indigo-100">
                        Manage applications, requests and assignments.
                    </p>
                </div>

            </div>

        </div>

        <p class="relative z-10 text-xs text-indigo-200">
            © {{ date('Y') }} Tuition Hub. All rights reserved.
        </p>

    </section>

    <section class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10 sm:px-6">

        <div class="w-full max-w-md">

            <div class="mb-8 lg:hidden">

                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center gap-3"
                >
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-600 font-black text-white shadow-lg shadow-indigo-200">
                        TH
                    </div>

                    <div>
                        <p class="font-black text-slate-900">
                            Tuition Hub
                        </p>

                        <p class="text-xs text-slate-500">
                            Smart Tuition Marketplace
                        </p>
                    </div>
                </a>

            </div>

            <div>

                <p class="text-sm font-semibold text-indigo-600">
                    Sign in
                </p>

                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                    Welcome back
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Enter your account details to continue.
                </p>

            </div>

            <div class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8">

                @if(session('status'))

                    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>

                @endif

                @if(session('error'))

                    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ session('error') }}
                    </div>

                @endif

                @if($errors->any())

                    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4">

                        <p class="text-sm font-semibold text-rose-800">
                            Please check the following:
                        </p>

                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-rose-700">

                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach

                        </ul>

                    </div>

                @endif

                <form
                    method="POST"
                    action="{{ route('login') }}"
                    class="space-y-5"
                >
                    @csrf

                    <div>

                        <label
                            for="email"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Email Address
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="you@example.com"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >

                    </div>

                    <div>

                        <div class="mb-2 flex items-center justify-between gap-3">

                            <label
                                for="password"
                                class="block text-sm font-semibold text-slate-700"
                            >
                                Password
                            </label>

                            @if(Route::has('password.request'))

                                <a
                                    href="{{ route('password.request') }}"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-700"
                                >
                                    Forgot password?
                                </a>

                            @endif

                        </div>

                        <div class="relative">

                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-20 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >

                            <button
                                type="button"
                                data-password-toggle="password"
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-xs font-semibold text-slate-500 transition hover:text-indigo-600"
                            >
                                Show
                            </button>

                        </div>

                    </div>

                    <label class="flex cursor-pointer items-center gap-3">

                        <input
                            id="remember"
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        >

                        <span class="text-sm text-slate-600">
                            Remember me
                        </span>

                    </label>

                    <button
                        type="submit"
                        class="flex w-full items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700"
                    >
                        Sign In
                    </button>

                </form>

                <div class="mt-7 border-t border-slate-100 pt-6 text-center">

                    <p class="text-sm text-slate-500">
                        Don't have an account?
                    </p>

                    <a
                        href="{{ route('register') }}"
                        class="mt-2 inline-flex font-semibold text-indigo-600 hover:text-indigo-700"
                    >
                        Create an account
                    </a>

                </div>

            </div>

        </div>

    </section>

</div>

<script>
    document
        .querySelectorAll('[data-password-toggle]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(
                    button.dataset.passwordToggle
                );

                if (!input) {
                    return;
                }

                const showing =
                    input.type === 'text';

                input.type =
                    showing
                        ? 'password'
                        : 'text';

                button.textContent =
                    showing
                        ? 'Show'
                        : 'Hide';
            });
        });
</script>

</body>
</html>