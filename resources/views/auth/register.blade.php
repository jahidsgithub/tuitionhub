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

    <title>Register - Tuition Hub</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="min-h-screen bg-slate-50">

<div class="min-h-screen lg:grid lg:grid-cols-2">

    <section class="relative hidden overflow-hidden bg-gradient-to-br from-slate-950 via-indigo-950 to-indigo-700 p-12 text-white lg:flex lg:flex-col lg:justify-between">

        <div class="absolute -right-28 -top-28 h-96 w-96 rounded-full bg-indigo-400/20 blur-3xl"></div>

        <div class="absolute -bottom-28 -left-28 h-96 w-96 rounded-full bg-violet-400/10 blur-3xl"></div>

        <a
            href="{{ route('home') }}"
            class="relative z-10 inline-flex items-center gap-3"
        >
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white font-black text-indigo-700">
                TH
            </div>

            <div>
                <p class="text-xl font-black">
                    Tuition Hub
                </p>

                <p class="text-xs text-indigo-200">
                    Smart Tuition Marketplace
                </p>
            </div>
        </a>

        <div class="relative z-10 max-w-xl">

            <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-indigo-100">
                Join Tuition Hub
            </span>

            <h1 class="mt-5 text-4xl font-black leading-tight xl:text-5xl">
                Create your account and start your tuition journey.
            </h1>

            <p class="mt-5 text-base leading-7 text-indigo-100">
                Register as a teacher or student and access a secure, organized tuition marketplace.
            </p>

            <div class="mt-8 space-y-3">

                <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                    <p class="font-bold">
                        For Teachers
                    </p>

                    <p class="mt-1 text-sm text-indigo-100">
                        Build your profile, find tuition opportunities and manage applications.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                    <p class="font-bold">
                        For Students / Guardians
                    </p>

                    <p class="mt-1 text-sm text-indigo-100">
                        Post tuition requirements and connect with verified teachers.
                    </p>
                </div>

            </div>

        </div>

        <p class="relative z-10 text-xs text-indigo-200">
            © {{ date('Y') }} Tuition Hub
        </p>

    </section>

    <section class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">

        <div class="w-full max-w-lg">

            <div class="mb-8 lg:hidden">

                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center gap-3"
                >
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-600 font-black text-white">
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
                    Create account
                </p>

                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                    Get started
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    Register as a teacher or student / guardian.
                </p>

            </div>

            <div class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8">

                @if($errors->any())

                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4">

                        <p class="text-sm font-semibold text-rose-800">
                            Please correct the following:
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
                    action="{{ route('register') }}"
                    class="space-y-5"
                >
                    @csrf

                    <div>

                        <label
                            for="role"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Register As
                        </label>

                        <select
                            id="role"
                            name="role"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >
                            <option value="">
                                Select Account Type
                            </option>

                            <option
                                value="teacher"
                                @selected(old('role') === 'teacher')
                            >
                                Teacher
                            </option>

                            <option
                                value="student"
                                @selected(old('role') === 'student')
                            >
                                Student / Guardian
                            </option>
                        </select>

                    </div>

                    <div>

                        <label
                            for="name"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Full Name
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Your full name"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >

                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">

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
                                autocomplete="email"
                                placeholder="you@example.com"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >

                        </div>

                        <div>

                            <label
                                for="phone"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Phone
                            </label>

                            <input
                                id="phone"
                                type="text"
                                name="phone"
                                value="{{ old('phone') }}"
                                required
                                autocomplete="tel"
                                placeholder="01XXXXXXXXX"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >

                        </div>

                    </div>

                    <div>

                        <label
                            for="password"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Password
                        </label>

                        <div class="relative">

                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Create a strong password"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-20 text-sm shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >

                            <button
                                type="button"
                                data-password-toggle="password"
                                class="absolute inset-y-0 right-0 px-4 text-xs font-semibold text-slate-500 hover:text-indigo-600"
                            >
                                Show
                            </button>

                        </div>

                    </div>

                    <div>

                        <label
                            for="password_confirmation"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Confirm Password
                        </label>

                        <div class="relative">

                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Repeat your password"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-20 text-sm shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >

                            <button
                                type="button"
                                data-password-toggle="password_confirmation"
                                class="absolute inset-y-0 right-0 px-4 text-xs font-semibold text-slate-500 hover:text-indigo-600"
                            >
                                Show
                            </button>

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700"
                    >
                        Create Account
                    </button>

                </form>

                <div class="mt-7 border-t border-slate-100 pt-6 text-center">

                    <p class="text-sm text-slate-500">
                        Already have an account?
                    </p>

                    <a
                        href="{{ route('login') }}"
                        class="mt-2 inline-flex font-semibold text-indigo-600 hover:text-indigo-700"
                    >
                        Sign in
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

                const visible =
                    input.type === 'text';

                input.type =
                    visible
                        ? 'password'
                        : 'text';

                button.textContent =
                    visible
                        ? 'Show'
                        : 'Hide';
            });
        });
</script>

</body>
</html>