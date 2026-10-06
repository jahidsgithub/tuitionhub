<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function edit(): View
    {
        return view('account.settings');
    }

    public function update(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',

                Rule::unique(
                    'users',
                    'email'
                )->ignore($user->id),
            ],

            'phone' => [
                'required',
                'string',
                'max:30',

                Rule::unique(
                    'users',
                    'phone'
                )->ignore($user->id),
            ],

            'current_password' => [
                'required',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Confirm Current Password
        |--------------------------------------------------------------------------
        */

        if (
            ! Hash::check(
                $validated['current_password'],
                $user->password
            )
        ) {
            return back()
                ->withInput(
                    $request->except(
                        'current_password'
                    )
                )
                ->withErrors([
                    'current_password' =>
                        'The current password is incorrect.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Detect Email Change
        |--------------------------------------------------------------------------
        */

        $newEmail = strtolower(
            trim(
                $validated['email']
            )
        );

        $emailChanged =
            $newEmail !==
            strtolower(
                $user->email
            );

        /*
        |--------------------------------------------------------------------------
        | Update Account
        |--------------------------------------------------------------------------
        */

        $user->name =
            trim(
                $validated['name']
            );

        $user->phone =
            trim(
                $validated['phone']
            );

        $user->email =
            $newEmail;

        /*
        |--------------------------------------------------------------------------
        | Email Change Requires Re-verification
        |--------------------------------------------------------------------------
        */

        if ($emailChanged) {
            $user->email_verified_at =
                null;
        }

        $user->save();

        /*
        |--------------------------------------------------------------------------
        | New Email Verification
        |--------------------------------------------------------------------------
        */

        if ($emailChanged) {
            $user
                ->sendEmailVerificationNotification();

            if (! $user->isAdmin()) {
                return redirect()
                    ->route(
                        'verification.notice'
                    )
                    ->with(
                        'success',
                        'Account updated. Please verify your new email address.'
                    );
            }

            return back()->with(
                'success',
                'Account updated. A verification email was sent to your new email address.'
            );
        }

        return back()->with(
            'success',
            'Account information updated successfully.'
        );
    }
}