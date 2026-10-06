<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reset Password - Tuition Hub</title>

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
                Create a new password
            </h1>

            <p class="mt-3 text-sm leading-6 text-slate-500">
                Choose a new password for your Tuition Hub account.
            </p>

        </div>

        <div class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8">

            @if($errors->any())

                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4">

                    <ul class="list-disc space-y-1 pl-5 text-sm text-rose-700">

                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            @endif

            <form
                method="POST"
                action="{{ route('password.update') }}"
                class="space-y-5"
            >
                @csrf

                <input
                    type="hidden"
                    name="token"
                    value="{{ $token }}"
                >

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
                        value="{{ old('email', $email) }}"
                        required
                        autocomplete="email"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    >

                </div>

                <div>

                    <label
                        for="password"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        New Password
                    </label>

                    <div class="relative">

                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Enter new password"
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
                        Confirm New Password
                    </label>

                    <div class="relative">

                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm new password"
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
                    Reset Password
                </button>

            </form>

        </div>

    </div>

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