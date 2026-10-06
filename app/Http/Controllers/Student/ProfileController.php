<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show student / guardian profile.
     */
    public function edit(): View
    {
        $user = auth()->user();

        $profile = StudentProfile::firstOrCreate([
            'user_id' => $user->id,
        ]);

        return view(
            'student.profile.edit',
            compact(
                'user',
                'profile'
            )
        );
    }

    /**
     * Update student / guardian profile.
     */
    public function update(
        Request $request
    ): RedirectResponse {
        $user = auth()->user();

        $profile = StudentProfile::firstOrCreate([
            'user_id' => $user->id,
        ]);

        $validated = $request->validate([
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'gender' => [
                'nullable',
                'in:male,female,other',
            ],

            'guardian_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'guardian_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'class_level' => [
                'nullable',
                'string',
                'max:100',
            ],

            'medium' => [
                'nullable',
                'in:bangla,english,english_version,madrasa,other',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $oldPhoto = $profile->profile_photo;

        $newPhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request
                ->file('profile_photo')
                ->store(
                    'student-profiles',
                    'public'
                );
        }

        $profile->update([
            'profile_photo' =>
                $newPhotoPath
                    ?: $profile->profile_photo,

            'gender' =>
                $validated['gender'] ?? null,

            'guardian_name' =>
                $validated['guardian_name'] ?? null,

            'guardian_phone' =>
                $validated['guardian_phone'] ?? null,

            'class_level' =>
                $validated['class_level'] ?? null,

            'medium' =>
                $validated['medium'] ?? null,

            'address' =>
                $validated['address'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Delete Old Photo
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

        return redirect()
            ->route('student.profile.edit')
            ->with(
                'success',
                'Profile updated successfully.'
            );
    }
}