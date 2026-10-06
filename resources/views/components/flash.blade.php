@if(session('success'))

    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-emerald-800">

        <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100">

            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m5 12 4 4L19 6"
                />
            </svg>

        </div>

        <div>

            <p class="text-sm font-semibold">
                Success
            </p>

            <p class="mt-0.5 text-sm text-emerald-700">
                {{ session('success') }}
            </p>

        </div>

    </div>

@endif

@if(session('error'))

    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-rose-800">

        <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-rose-100">

            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    d="M6 18 18 6M6 6l12 12"
                />
            </svg>

        </div>

        <div>

            <p class="text-sm font-semibold">
                Action could not be completed
            </p>

            <p class="mt-0.5 text-sm text-rose-700">
                {{ session('error') }}
            </p>

        </div>

    </div>

@endif

@if($errors->any())

    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4">

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