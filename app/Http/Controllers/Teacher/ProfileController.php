<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show teacher profile edit page.
     */
    public function edit(): View
    {
        $user = auth()->user();

        $profile = TeacherProfile::firstOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'experience_years' => 0,
                'teaching_mode' => 'offline',
                'is_verified' => false,
                'is_available' => true,
            ]
        );

        $profile->load([
            'subjects',
            'locations',
        ]);

        $subjects = Subject::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $locations = Location::query()
            ->where('status', true)
            ->orderBy('district')
            ->orderBy('area')
            ->get();

        return view(
            'teacher.profile.edit',
            compact(
                'user',
                'profile',
                'subjects',
                'locations'
            )
        );
    }

    /**
     * Update teacher profile.
     */
    public function update(
        Request $request
    ): RedirectResponse {
        $user = auth()->user();

        $profile = TeacherProfile::firstOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'experience_years' => 0,
                'teaching_mode' => 'offline',
                'is_verified' => false,
                'is_available' => true,
            ]
        );

        $validated = $request->validate([
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'gender' => [
                'required',
                'in:male,female,other',
            ],

            'university' => [
                'required',
                'string',
                'max:255',
            ],

            'department' => [
                'required',
                'string',
                'max:255',
            ],

            'degree' => [
                'nullable',
                'string',
                'max:255',
            ],

            'experience_years' => [
                'required',
                'integer',
                'min:0',
                'max:60',
            ],

            'bio' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'expected_salary_min' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999',
            ],

            'expected_salary_max' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999',
                'gte:expected_salary_min',
            ],

            'teaching_mode' => [
                'required',
                'in:offline,online,both',
            ],

            'subjects' => [
                'required',
                'array',
                'min:1',
            ],

            'subjects.*' => [
                'integer',
                'exists:subjects,id',
            ],

            'locations' => [
                'nullable',
                'array',
            ],

            'locations.*' => [
                'integer',
                'exists:locations,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Detect Verification Sensitive Changes
        |--------------------------------------------------------------------------
        */

        $oldSubjectIds = $profile
            ->subjects()
            ->pluck('subjects.id')
            ->sort()
            ->values()
            ->all();

        $oldLocationIds = $profile
            ->locations()
            ->pluck('locations.id')
            ->sort()
            ->values()
            ->all();

        $newSubjectIds = collect(
            $validated['subjects']
        )
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $newLocationIds = collect(
            $validated['locations'] ?? []
        )
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $sensitiveChanged =
            $profile->gender !== $validated['gender']
            || $profile->university !== $validated['university']
            || $profile->department !== $validated['department']
            || $profile->degree !== ($validated['degree'] ?? null)
            || (int) $profile->experience_years !== (int) $validated['experience_years']
            || $profile->teaching_mode !== $validated['teaching_mode']
            || $oldSubjectIds !== $newSubjectIds
            || $oldLocationIds !== $newLocationIds
            || $request->hasFile('profile_photo');

        $wasVerified = (bool) $profile->is_verified;

        $oldPhoto = $profile->profile_photo;

        $newPhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request
                ->file('profile_photo')
                ->store(
                    'teacher-profiles',
                    'public'
                );
        }

        DB::transaction(
            function () use (
                $request,
                $profile,
                $validated,
                $newPhotoPath,
                $sensitiveChanged,
                $wasVerified
            ) {
                $profile->update([
                    'profile_photo' =>
                        $newPhotoPath
                            ?: $profile->profile_photo,

                    'gender' =>
                        $validated['gender'],

                    'university' =>
                        $validated['university'],

                    'department' =>
                        $validated['department'],

                    'degree' =>
                        $validated['degree'] ?? null,

                    'experience_years' =>
                        $validated['experience_years'],

                    'bio' =>
                        $validated['bio'] ?? null,

                    'expected_salary_min' =>
                        $validated['expected_salary_min'] ?? null,

                    'expected_salary_max' =>
                        $validated['expected_salary_max'] ?? null,

                    'teaching_mode' =>
                        $validated['teaching_mode'],

                    'is_available' =>
                        $request->boolean(
                            'is_available'
                        ),

                    /*
                    |--------------------------------------------------------------------------
                    | Verified Profile Changed → Review Again
                    |--------------------------------------------------------------------------
                    */

                    'is_verified' =>
                        (
                            $wasVerified &&
                            $sensitiveChanged
                        )
                            ? false
                            : $profile->is_verified,
                ]);

                $profile
                    ->subjects()
                    ->sync(
                        $validated['subjects']
                    );

                $profile
                    ->locations()
                    ->sync(
                        $validated['locations'] ?? []
                    );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Delete Previous Photo After Successful Save
        |--------------------------------------------------------------------------
        */

        if (
            $newPhotoPath &&
            $oldPhoto &&
            $oldPhoto !== $newPhotoPath
        ) {
            Storage::disk('public')
                ->delete($oldPhoto);
        }

        /*
        |--------------------------------------------------------------------------
        | Admin Re-verification Notification
        |--------------------------------------------------------------------------
        */

        if (
            $wasVerified &&
            $sensitiveChanged
        ) {
            AdminNotificationService::send(
                'Teacher Profile Requires Re-verification',
                $user->name
                    .' updated verified profile information. The profile now requires review again.',
                route(
                    'admin.teachers.index',
                    [
                        'status' => 'pending',
                        'search' => $user->email,
                    ]
                ),
                'teacher_verification'
            );

            return redirect()
                ->route('teacher.profile.edit')
                ->with(
                    'success',
                    'Profile updated successfully. Because verified information was changed, your profile has been sent for admin re-verification.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Notify Admin For First Profile Completion / Update
        |--------------------------------------------------------------------------
        */

        if (! $profile->is_verified) {
            AdminNotificationService::send(
                'Teacher Profile Ready for Review',
                $user->name
                    .' updated the teacher profile and is waiting for verification.',
                route(
                    'admin.teachers.index',
                    [
                        'status' => 'pending',
                        'search' => $user->email,
                    ]
                ),
                'teacher_verification'
            );
        }

        return redirect()
            ->route('teacher.profile.edit')
            ->with(
                'success',
                'Profile updated successfully.'
            );
    }
}