<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeacherProfileEditRequest;
use App\Services\AdminNotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();

        $profile = $this->profileFor(
            $user->id
        );

        $profile->load([
            'subjects',
            'locations',
            'pendingProfileEditRequest',
        ]);

        $recentEditRequests =
            $profile
                ->profileEditRequests()
                ->latest()
                ->limit(5)
                ->get();

        return view(
            'teacher.profile.edit',
            compact(
                'user',
                'profile',
                'recentEditRequests'
            )
        );
    }

    public function requestEdit(): View|RedirectResponse
    {
        $user = auth()->user();

        $profile = $this->profileFor(
            $user->id
        );

        $profile->load([
            'subjects',
            'locations',
        ]);

        $pendingRequest =
            $profile
                ->profileEditRequests()
                ->where(
                    'status',
                    TeacherProfileEditRequest::STATUS_PENDING
                )
                ->latest()
                ->first();

        if ($pendingRequest) {
            return redirect()
                ->route(
                    'teacher.profile.edit'
                )
                ->with(
                    'error',
                    'You already have a pending profile edit request. Please wait for admin review.'
                );
        }

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
            'teacher.profile.request-edit',
            compact(
                'user',
                'profile',
                'subjects',
                'locations'
            )
        );
    }

    public function storeEditRequest(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        $profile = $this->profileFor(
            $user->id
        );

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
                        'teacher-profile-edit-requests',
                        'public'
                    );
        }

        try {
            $editRequest =
                DB::transaction(
                    function () use (
                        $profile,
                        $user,
                        $validated,
                        $isAvailable,
                        $newPhotoPath
                    ) {
                        $lockedProfile =
                            TeacherProfile::query()
                                ->lockForUpdate()
                                ->findOrFail(
                                    $profile->id
                                );

                        $hasPending =
                            TeacherProfileEditRequest::query()
                                ->where(
                                    'teacher_profile_id',
                                    $lockedProfile->id
                                )
                                ->where(
                                    'status',
                                    TeacherProfileEditRequest::STATUS_PENDING
                                )
                                ->lockForUpdate()
                                ->exists();

                        if ($hasPending) {
                            return null;
                        }

                        $subjectIds =
                            collect(
                                $validated['subjects']
                            )
                                ->map(
                                    fn ($id) =>
                                        (int) $id
                                )
                                ->unique()
                                ->values()
                                ->all();

                        $locationIds =
                            collect(
                                $validated['locations']
                                    ?? []
                            )
                                ->map(
                                    fn ($id) =>
                                        (int) $id
                                )
                                ->unique()
                                ->values()
                                ->all();

                        return TeacherProfileEditRequest::create([
                            'teacher_profile_id' =>
                                $lockedProfile->id,

                            'requested_by_user_id' =>
                                $user->id,

                            'requested_data' => [
                                'gender' =>
                                    $validated['gender'],

                                'university' =>
                                    $validated['university'],

                                'department' =>
                                    $validated['department'],

                                'degree' =>
                                    $validated['degree']
                                    ?? null,

                                'experience_years' =>
                                    (int) $validated[
                                        'experience_years'
                                    ],

                                'bio' =>
                                    $validated['bio']
                                    ?? null,

                                'expected_salary_min' =>
                                    $validated[
                                        'expected_salary_min'
                                    ] ?? null,

                                'expected_salary_max' =>
                                    $validated[
                                        'expected_salary_max'
                                    ] ?? null,

                                'teaching_mode' =>
                                    $validated[
                                        'teaching_mode'
                                    ],

                                'is_available' =>
                                    $isAvailable,

                                'subject_ids' =>
                                    $subjectIds,

                                'location_ids' =>
                                    $locationIds,
                            ],

                            'profile_photo_path' =>
                                $newPhotoPath,

                            'status' =>
                                TeacherProfileEditRequest::STATUS_PENDING,

                            'active_key' =>
                                'teacher_profile_'
                                .$lockedProfile->id,
                        ]);
                    }
                );
        } catch (QueryException $exception) {
            if ($newPhotoPath) {
                Storage::disk(
                    'public'
                )->delete(
                    $newPhotoPath
                );
            }

            $sqlState =
                $exception
                    ->errorInfo[0]
                    ?? null;

            $driverCode =
                (int) (
                    $exception
                        ->errorInfo[1]
                    ?? 0
                );

            $isDuplicate =
                $sqlState === '23000'
                ||
                $sqlState === '23505'
                ||
                $driverCode === 1062
                ||
                str_contains(
                    strtolower(
                        $exception->getMessage()
                    ),
                    'unique'
                );

            if ($isDuplicate) {
                return redirect()
                    ->route(
                        'teacher.profile.edit'
                    )
                    ->with(
                        'error',
                        'You already have a pending profile edit request.'
                    );
            }

            throw $exception;
        }

        if (! $editRequest) {
            if ($newPhotoPath) {
                Storage::disk(
                    'public'
                )->delete(
                    $newPhotoPath
                );
            }

            return redirect()
                ->route(
                    'teacher.profile.edit'
                )
                ->with(
                    'error',
                    'You already have a pending profile edit request.'
                );
        }

        AdminNotificationService::send(
            'Teacher Profile Edit Request',
            $user->name
                .' submitted a profile edit request for admin review.',
            route(
                'admin.teacher-profile-edit-requests.index',
                [
                    'status' =>
                        'pending',

                    'search' =>
                        $user->email,
                ]
            ),
            'teacher_profile_edit_request'
        );

        return redirect()
            ->route(
                'teacher.profile.edit'
            )
            ->with(
                'success',
                'Your profile edit request was submitted successfully. Your current profile will remain unchanged until an admin approves the request.'
            );
    }

    private function profileFor(
        int $userId
    ): TeacherProfile {
        return TeacherProfile::firstOrCreate(
            [
                'user_id' =>
                    $userId,
            ],
            [
                'experience_years' =>
                    0,

                'teaching_mode' =>
                    'offline',

                'is_verified' =>
                    false,

                'is_available' =>
                    true,
            ]
        );
    }
}