<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Forgot Password - Tuition Hub</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="min-h-screen bg-slate-50">

<div class="flex min-h-screen items-center justify-center px-4 py-10">

    <div class="w-full max-w-md">

        <div class="text-center">

            <a
                href="{{ route('home') }}"
                class="inline-flex items-center gap-3"
            >
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 font-black text-white shadow-lg shadow-indigo-200">
                    TH
                </div>

                <span class="text-xl font-black text-slate-900">
                    Tuition Hub
                </span>
            </a>

            <h1 class="mt-8 text-3xl font-black tracking-tight text-slate-900">
                Forgot your password?
            </h1>

            <p class="mt-3 text-sm leading-6 text-slate-500">
                Enter your account email and we'll send you a secure password reset link.
            </p>

        </div>

        <div class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8">

            @if(session('success'))

                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>

            @endif

            @if(session('status'))

                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>

            @endif

            @if($errors->any())

                <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4">

                    <ul class="list-disc space-y-1 pl-5 text-sm text-rose-700">

                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            @endif

            <form
                method="POST"
                action="{{ route('password.email') }}"
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
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700"
                >
                    Send Reset Link
                </button>

            </form>

            <div class="mt-7 border-t border-slate-100 pt-6 text-center">

                <a
                    href="{{ route('login') }}"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
                >
                    ← Back to Login
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>