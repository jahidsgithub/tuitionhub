<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Verify Email - Tuition Hub</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="min-h-screen bg-slate-50">

<div class="flex min-h-screen items-center justify-center px-4 py-10">

    <div class="w-full max-w-lg">

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

        </div>

        <div class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-xl shadow-slate-200/50 sm:p-8">

            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">

                <svg
                    class="h-8 w-8"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.69 5.793a1.875 1.875 0 0 1-2.12 0L2.25 6.75"
                    />
                </svg>

            </div>

            <h1 class="mt-5 text-3xl font-black tracking-tight text-slate-900">
                Verify your email
            </h1>

            <p class="mt-3 text-sm leading-6 text-slate-500">
                We sent a verification link to
            </p>

            <p class="mt-2 font-bold text-slate-900">
                {{ auth()->user()->email }}
            </p>

            @if(session('success'))

                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-left text-sm text-emerald-700">
                    {{ session('success') }}
                </div>

            @endif

            @if(session('status'))

                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-left text-sm text-emerald-700">
                    {{ session('status') }}
                </div>

            @endif

            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-left">

                <p class="text-sm font-semibold text-slate-800">
                    What to do next
                </p>

                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Open your verification email and click the verification link. After verification you can continue to your account.
                </p>

                @if(app()->environment('local'))

                    <div class="mt-4 rounded-xl border border-indigo-100 bg-indigo-50 p-3">

                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                            Local Development
                        </p>

                        <p class="mt-1 text-sm text-indigo-700">
                            Mailpit:
                            <span class="font-semibold">
                                http://127.0.0.1:8025
                            </span>
                        </p>

                    </div>

                @endif

            </div>

            <form
                method="POST"
                action="{{ route('verification.send') }}"
                class="mt-6"
            >
                @csrf

                <button
                    type="submit"
                    class="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700"
                >
                    Resend Verification Email
                </button>

            </form>

            <form
                method="POST"
                action="{{ route('logout') }}"
                class="mt-3"
            >
                @csrf

                <button
                    type="submit"
                    class="w-full rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Sign Out
                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>