<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TeacherProfileController extends Controller
{
    public function edit(
        TeacherProfile $teacher
    ): View {
        $teacher->load([
            'user',
            'subjects',
            'locations',
            'pendingProfileEditRequest',
        ]);

        $subjects = Subject::query()
            ->where(
                'status',
                true
            )
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $locations = Location::query()
            ->where(
                'status',
                true
            )
            ->orderBy('division')
            ->orderBy('district')
            ->orderBy('area')
            ->get();

        return view(
            'admin.teachers.edit',
            compact(
                'teacher',
                'subjects',
                'locations'
            )
        );
    }

    public function update(
        Request $request,
        TeacherProfile $teacher
    ): RedirectResponse {
        $teacher->load([
            'user',
            'subjects',
            'locations',
        ]);

        $validated =
            $request->validate([
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

        if (
            in_array(
                $validated['teaching_mode'],
                [
                    'offline',
                    'both',
                ],
                true
            )
            &&
            empty(
                $validated['locations'] ?? []
            )
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Please select at least one teaching location for offline or both teaching mode.'
                );
        }

        $isAvailable =
            $request->boolean(
                'is_available'
            );

        $newPhotoPath = null;

        if (
            $request->hasFile(
                'profile_photo'
            )
        ) {
            $newPhotoPath =
                $request
                    ->file(
                        'profile_photo'
                    )
                    ->store(
                        'teacher-profiles',
                        'public'
                    );
        }

        $oldPhoto =
            $teacher->profile_photo;

        try {
            DB::transaction(
                function () use (
                    $teacher,
                    $validated,
                    $isAvailable,
                    $newPhotoPath
                ) {
                    $lockedTeacher =
                        TeacherProfile::query()
                            ->lockForUpdate()
                            ->findOrFail(
                                $teacher->id
                            );

                    $lockedTeacher->update([
                        'profile_photo' =>
                            $newPhotoPath
                                ?: $lockedTeacher
                                    ->profile_photo,

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
                            $validated[
                                'expected_salary_min'
                            ] ?? null,

                        'expected_salary_max' =>
                            $validated[
                                'expected_salary_max'
                            ] ?? null,

                        'teaching_mode' =>
                            $validated['teaching_mode'],

                        'is_available' =>
                            $isAvailable,
                    ]);

                    $lockedTeacher
                        ->subjects()
                        ->sync(
                            $validated['subjects']
                        );

                    $lockedTeacher
                        ->locations()
                        ->sync(
                            $validated['locations'] ?? []
                        );
                }
            );
        } catch (\Throwable $exception) {
            if ($newPhotoPath) {
                Storage::disk(
                    'public'
                )->delete(
                    $newPhotoPath
                );
            }

            throw $exception;
        }

        if (
            $newPhotoPath &&
            $oldPhoto &&
            $oldPhoto !== $newPhotoPath
        ) {
            Storage::disk(
                'public'
            )->delete(
                $oldPhoto
            );
        }

        $teacher->refresh();

        $teacher->load('user');

        if ($teacher->user) {
            UserNotificationService::send(
                $teacher->user,
                'Teacher Profile Updated',
                'An admin updated your teacher profile.',
                route(
                    'teacher.profile.edit'
                ),
                'teacher_profile'
            );
        }

        return redirect()
            ->route(
                'admin.teachers.edit',
                $teacher
            )
            ->with(
                'success',
                'Teacher profile updated successfully.'
            );
    }
}