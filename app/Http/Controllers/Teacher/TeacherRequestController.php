<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherProfile;
use App\Models\TeacherRequest;
use App\Models\TuitionAssignment;
use App\Models\TuitionPost;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeacherRequestController extends Controller
{
    public function index(): View
    {
        $profile = auth()
            ->user()
            ->teacherProfile;

        if (! $profile) {
            return view(
                'teacher.requests.index',
                [
                    'requests' =>
                        TeacherRequest::query()
                            ->whereRaw('1 = 0')
                            ->paginate(15),
                ]
            );
        }

        $requests = TeacherRequest::query()
            ->with([
                'student.studentProfile',
                'tuitionPost.location',
                'tuitionPost.subjects',
            ])
            ->where(
                'teacher_profile_id',
                $profile->id
            )
            ->latest()
            ->paginate(15);

        return view(
            'teacher.requests.index',
            compact('requests')
        );
    }

    public function accept(
        TeacherRequest $teacherRequest
    ): RedirectResponse {
        $profile = auth()
            ->user()
            ->teacherProfile;

        if (! $profile) {
            return redirect()
                ->route(
                    'teacher.profile.edit'
                )
                ->with(
                    'error',
                    'Teacher profile not found.'
                );
        }

        abort_unless(
            $teacherRequest->teacher_profile_id ===
            $profile->id,
            403
        );

        $result = DB::transaction(
            function () use (
                $teacherRequest,
                $profile
            ) {
                /*
                |--------------------------------------------------------------------------
                | Lock Teacher First
                |--------------------------------------------------------------------------
                */

                $lockedTeacher =
                    TeacherProfile::query()
                        ->with('user')
                        ->lockForUpdate()
                        ->findOrFail(
                            $profile->id
                        );

                if (
                    ! $lockedTeacher->is_verified
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'Your teacher profile is not currently verified.',
                    ];
                }

                if (
                    ! $lockedTeacher->is_available
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'Your teacher profile is currently unavailable.',
                    ];
                }

                if (
                    ! $lockedTeacher->user ||
                    $lockedTeacher->user->status !==
                        'active'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'Your account is not currently active.',
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Lock Request
                |--------------------------------------------------------------------------
                */

                $lockedRequest =
                    TeacherRequest::query()
                        ->with([
                            'student',
                            'tuitionPost',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $teacherRequest->id
                        );

                if (
                    $lockedRequest->teacher_profile_id !==
                    $lockedTeacher->id
                ) {
                    abort(403);
                }

                if (
                    $lockedRequest->status !==
                    'pending'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'Only pending requests can be accepted.',
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Student Must Still Be Active
                |--------------------------------------------------------------------------
                */

                if (
                    ! $lockedRequest->student ||
                    $lockedRequest->student->status !==
                        'active'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'The student account is no longer active.',
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Linked Tuition Must Still Be Valid
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedRequest->tuition_post_id
                ) {
                    $lockedTuition =
                        TuitionPost::query()
                            ->lockForUpdate()
                            ->findOrFail(
                                $lockedRequest
                                    ->tuition_post_id
                            );

                    if (
                        $lockedTuition->user_id !==
                        $lockedRequest
                            ->student_user_id
                    ) {
                        return [
                            'success' => false,
                            'message' =>
                                'The linked tuition no longer belongs to this student.',
                        ];
                    }

                    if (
                        $lockedTuition->status !==
                        'published'
                    ) {
                        return [
                            'success' => false,
                            'message' =>
                                'The linked tuition is no longer available.',
                        ];
                    }

                    $assignmentExists =
                        TuitionAssignment::query()
                            ->where(
                                'tuition_post_id',
                                $lockedTuition->id
                            )
                            ->exists();

                    if ($assignmentExists) {
                        return [
                            'success' => false,
                            'message' =>
                                'This tuition already has a final teacher assignment.',
                        ];
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Accept
                |--------------------------------------------------------------------------
                */

                $lockedRequest->update([
                    'status' =>
                        'accepted',
                ]);

                return [
                    'success' => true,
                    'request_id' =>
                        $lockedRequest->id,
                ];
            },
            3
        );

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        $acceptedRequest =
            TeacherRequest::query()
                ->with([
                    'student',
                    'tuitionPost',
                ])
                ->find(
                    $result['request_id']
                );

        if ($acceptedRequest?->student) {
            UserNotificationService::send(
                $acceptedRequest->student,
                'Teacher Accepted Your Request',
                auth()->user()->name
                    .' accepted your direct tuition request.'
                    .(
                        $acceptedRequest
                            ->tuitionPost
                            ?->title
                            ? ' for '
                                .$acceptedRequest
                                    ->tuitionPost
                                    ->title
                                .'.'
                            : '.'
                    ),
                route(
                    'student.teacher-requests.index'
                ),
                'teacher_request'
            );
        }

        return back()->with(
            'success',
            'Teacher request accepted successfully. The student can now confirm you.'
        );
    }

    public function reject(
        TeacherRequest $teacherRequest
    ): RedirectResponse {
        $profile = auth()
            ->user()
            ->teacherProfile;

        if (! $profile) {
            return redirect()
                ->route(
                    'teacher.profile.edit'
                )
                ->with(
                    'error',
                    'Teacher profile not found.'
                );
        }

        abort_unless(
            $teacherRequest->teacher_profile_id ===
            $profile->id,
            403
        );

        $result = DB::transaction(
            function () use (
                $teacherRequest,
                $profile
            ) {
                /*
                |--------------------------------------------------------------------------
                | Lock Teacher
                |--------------------------------------------------------------------------
                */

                $lockedTeacher =
                    TeacherProfile::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $profile->id
                        );

                /*
                |--------------------------------------------------------------------------
                | Lock Request
                |--------------------------------------------------------------------------
                */

                $lockedRequest =
                    TeacherRequest::query()
                        ->with('student')
                        ->lockForUpdate()
                        ->findOrFail(
                            $teacherRequest->id
                        );

                if (
                    $lockedRequest->teacher_profile_id !==
                    $lockedTeacher->id
                ) {
                    abort(403);
                }

                if (
                    $lockedRequest->status !==
                    'pending'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'Only pending requests can be rejected.',
                    ];
                }

                $lockedRequest->update([
                    'status' =>
                        'rejected',
                ]);

                return [
                    'success' => true,
                    'request_id' =>
                        $lockedRequest->id,
                ];
            },
            3
        );

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        $rejectedRequest =
            TeacherRequest::query()
                ->with([
                    'student',
                    'tuitionPost',
                ])
                ->find(
                    $result['request_id']
                );

        if ($rejectedRequest?->student) {
            UserNotificationService::send(
                $rejectedRequest->student,
                'Teacher Request Declined',
                auth()->user()->name
                    .' declined your direct tuition request.'
                    .(
                        $rejectedRequest
                            ->tuitionPost
                            ?->title
                            ? ' for '
                                .$rejectedRequest
                                    ->tuitionPost
                                    ->title
                                .'.'
                            : '.'
                    ),
                route(
                    'student.teacher-requests.index'
                ),
                'teacher_request'
            );
        }

        return back()->with(
            'success',
            'Teacher request rejected.'
        );
    }
}