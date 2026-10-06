<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view(
            'auth.register'
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
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
                'unique:users,email',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
                'unique:users,phone',
            ],

            'role' => [
                'required',
                'in:teacher,student',
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        $user = DB::transaction(
            function () use ($validated) {
                $user = User::create([
                    'name' =>
                        $validated['name'],

                    'email' =>
                        $validated['email'],

                    'phone' =>
                        $validated['phone'],

                    'role' =>
                        $validated['role'],

                    'status' =>
                        'active',

                    'password' =>
                        $validated['password'],
                ]);

                if ($user->isTeacher()) {
                    TeacherProfile::create([
                        'user_id' =>
                            $user->id,

                        'experience_years' =>
                            0,

                        'teaching_mode' =>
                            'offline',

                        'is_verified' =>
                            false,

                        'is_available' =>
                            true,
                    ]);
                }

                if ($user->isStudent()) {
                    StudentProfile::create([
                        'user_id' =>
                            $user->id,
                    ]);
                }

                return $user;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Trigger Laravel Verification Email
        |--------------------------------------------------------------------------
        */

        event(
            new Registered($user)
        );

        if ($user->isTeacher()) {
            AdminNotificationService::send(
                'New Teacher Registration',
                $user->name
                    .' registered as a teacher and is awaiting verification.',
                route(
                    'admin.teachers.index',
                    [
                        'status' =>
                            'pending',

                        'search' =>
                            $user->email,
                    ]
                ),
                'teacher_verification'
            );
        }

        Auth::login($user);

        $request
            ->session()
            ->regenerate();

        return redirect()
            ->route(
                'verification.notice'
            );
    }
}