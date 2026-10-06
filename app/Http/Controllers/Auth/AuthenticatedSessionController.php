<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show login page.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate user.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
            ],

            'remember' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Normalize Email
        |--------------------------------------------------------------------------
        */

        $email = Str::lower(
            trim(
                $validated['email']
            )
        );

        $throttleKey =
            $this->throttleKey(
                $email,
                $request
            );

        /*
        |--------------------------------------------------------------------------
        | Login Rate Limit
        |--------------------------------------------------------------------------
        |
        | Maximum 5 failed attempts.
        | After that the login is temporarily blocked for 60 seconds.
        |
        */

        if (
            RateLimiter::tooManyAttempts(
                $throttleKey,
                5
            )
        ) {
            $seconds =
                RateLimiter::availableIn(
                    $throttleKey
                );

            throw ValidationException::withMessages([
                'email' =>
                    'Too many login attempts. Please try again in '
                    .$seconds
                    .' seconds.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Attempt Login
        |--------------------------------------------------------------------------
        */

        $remember =
            (bool) (
                $validated['remember']
                ?? false
            );

        $authenticated = Auth::attempt(
            [
                'email' =>
                    $email,

                'password' =>
                    $validated['password'],
            ],
            $remember
        );

        if (! $authenticated) {
            /*
            |--------------------------------------------------------------------------
            | Record Failed Attempt
            |--------------------------------------------------------------------------
            */

            RateLimiter::hit(
                $throttleKey,
                60
            );

            throw ValidationException::withMessages([
                'email' =>
                    'The provided email or password is incorrect.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Authenticated User
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Account Status Protection
        |--------------------------------------------------------------------------
        */

        if (! $user->isActive()) {
            Auth::logout();

            $request
                ->session()
                ->invalidate();

            $request
                ->session()
                ->regenerateToken();

            throw ValidationException::withMessages([
                'email' =>
                    $user->status === 'suspended'
                        ? 'Your account is currently suspended.'
                        : 'Your account is currently inactive.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Successful Login
        |--------------------------------------------------------------------------
        */

        RateLimiter::clear(
            $throttleKey
        );

        $request
            ->session()
            ->regenerate();

        /*
        |--------------------------------------------------------------------------
        | Email Verification
        |--------------------------------------------------------------------------
        |
        | Admin is currently exempt so the existing Super Admin account
        | cannot accidentally be locked out.
        |
        */

        if (
            ! $user->isAdmin() &&
            ! $user->hasVerifiedEmail()
        ) {
            return redirect()
                ->route(
                    'verification.notice'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Role Redirect
        |--------------------------------------------------------------------------
        */

        if ($user->isAdmin()) {
            return redirect()
                ->intended(
                    route(
                        'admin.dashboard'
                    )
                );
        }

        if ($user->isTeacher()) {
            return redirect()
                ->intended(
                    route(
                        'teacher.dashboard'
                    )
                );
        }

        if ($user->isStudent()) {
            return redirect()
                ->intended(
                    route(
                        'student.dashboard'
                    )
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Unknown Role
        |--------------------------------------------------------------------------
        */

        Auth::logout();

        $request
            ->session()
            ->invalidate();

        $request
            ->session()
            ->regenerateToken();

        abort(403);
    }

    /**
     * Logout current user.
     */
    public function destroy(
        Request $request
    ): RedirectResponse {
        Auth::guard('web')
            ->logout();

        $request
            ->session()
            ->invalidate();

        $request
            ->session()
            ->regenerateToken();

        return redirect()
            ->route('login')
            ->with(
                'success',
                'You have been logged out successfully.'
            );
    }

    /**
     * Generate login throttle key.
     */
    private function throttleKey(
        string $email,
        Request $request
    ): string {
        return Str::transliterate(
            Str::lower($email)
            .'|'
            .$request->ip()
        );
    }
}