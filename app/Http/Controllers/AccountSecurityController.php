<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountSecurityController extends Controller
{
    public function edit(): View
    {
        return view('account.security');
    }

    public function update(
        Request $request
    ): RedirectResponse {
        $action = $request->input(
            'action',
            'change_password'
        );

        if ($action === 'logout_other_devices') {
            return $this->logoutOtherDevices(
                $request
            );
        }

        return $this->changePassword(
            $request
        );
    }

    private function changePassword(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Verify Current Password
        |--------------------------------------------------------------------------
        */

        if (
            ! Hash::check(
                $validated['current_password'],
                $user->password
            )
        ) {
            throw ValidationException::withMessages([
                'current_password' =>
                    'The current password is incorrect.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Current Password Reuse
        |--------------------------------------------------------------------------
        */

        if (
            Hash::check(
                $validated['password'],
                $user->password
            )
        ) {
            throw ValidationException::withMessages([
                'password' =>
                    'Your new password must be different from your current password.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Save Password
        |--------------------------------------------------------------------------
        */

        $user->update([
            'password' =>
                $validated['password'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Refresh Current Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();

        return back()->with(
            'success',
            'Password changed successfully.'
        );
    }

    private function logoutOtherDevices(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'device_password' => [
                'required',
                'string',
            ],
        ]);

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Verify Password
        |--------------------------------------------------------------------------
        */

        if (
            ! Hash::check(
                $validated['device_password'],
                $user->password
            )
        ) {
            throw ValidationException::withMessages([
                'device_password' =>
                    'The password is incorrect.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Logout Other Sessions
        |--------------------------------------------------------------------------
        |
        | Current browser remains logged in.
        |
        */

        Auth::logoutOtherDevices(
            $validated['device_password']
        );

        /*
        |--------------------------------------------------------------------------
        | Regenerate Current Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();

        return back()->with(
            'success',
            'Other logged-in devices have been signed out.'
        );
    }
}