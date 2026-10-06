<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TeacherProfile;
use App\Models\TuitionApplication;
use App\Models\TuitionAssignment;
use App\Models\TuitionPost;
use App\Services\UserNotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TuitionApplicationController extends Controller
{
    /**
     * Show applications for a student's tuition.
     */
    public function index(
        TuitionPost $tuition
    ): View {
        $this->authorizeTuitionOwnership(
            $tuition
        );

        $tuition->load([
            'location',
            'subjects',
            'assignment.teacherProfile.user',
        ]);

        $applications =
            TuitionApplication::query()
                ->with([
                    'teacherProfile.user',
                    'teacherProfile.subjects',
                    'teacherProfile.locations',
                ])
                ->where(
                    'tuition_post_id',
                    $tuition->id
                )
                ->latest()
                ->paginate(15);

        return view(
            'student.tuitions.applications.index',
            compact(
                'tuition',
                'applications'
            )
        );
    }

    /**
     * Shortlist an application.
     */
    public function shortlist(
        TuitionPost $tuition,
        TuitionApplication $application
    ): RedirectResponse {
        $this->authorizeTuitionOwnership(
            $tuition
        );

        $result = DB::transaction(
            function () use (
                $tuition,
                $application
            ) {
                /*
                |--------------------------------------------------------------------------
                | Lock Tuition
                |--------------------------------------------------------------------------
                */

                $lockedTuition =
                    TuitionPost::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $tuition->id
                        );

                $this->authorizeLockedTuition(
                    $lockedTuition
                );

                /*
                |--------------------------------------------------------------------------
                | Tuition Must Still Accept Changes
                |--------------------------------------------------------------------------
                */

                if (
                    in_array(
                        $lockedTuition->status,
                        [
                            'filled',
                            'closed',
                        ],
                        true
                    )
                ) {
                    return [
                        'success' => false,

                        'message' =>
                            'This tuition is no longer accepting application changes.',
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Lock Application
                |--------------------------------------------------------------------------
                */

                $lockedApplication =
                    TuitionApplication::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $application->id
                        );

                /*
                |--------------------------------------------------------------------------
                | Application Must Belong to Tuition
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedApplication
                        ->tuition_post_id !==
                    $lockedTuition->id
                ) {
                    abort(404);
                }

                /*
                |--------------------------------------------------------------------------
                | Status Check
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedApplication
                        ->status !==
                    'pending'
                ) {
                    return [
                        'success' => false,

                        'message' =>
                            'This application cannot be shortlisted.',
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Teacher Still Eligible
                |--------------------------------------------------------------------------
                */

                $eligibility =
                    $this->checkTeacherEligibility(
                        $lockedApplication
                            ->teacher_profile_id
                    );

                if (
                    ! $eligibility['eligible']
                ) {
                    return [
                        'success' => false,

                        'message' =>
                            $eligibility['message'],
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Shortlist
                |--------------------------------------------------------------------------
                */

                $lockedApplication
                    ->update([
                        'status' =>
                            'shortlisted',
                    ]);

                return [
                    'success' => true,

                    'application_id' =>
                        $lockedApplication->id,
                ];
            }
        );

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Notify Teacher After Commit
        |--------------------------------------------------------------------------
        */

        $application =
            TuitionApplication::query()
                ->with([
                    'teacherProfile.user',
                    'tuitionPost',
                ])
                ->find(
                    $result[
                        'application_id'
                    ]
                );

        if (
            $application
                ?->teacherProfile
                ?->user
        ) {
            UserNotificationService::send(
                $application
                    ->teacherProfile
                    ->user,

                'Application Shortlisted',

                'Your application for '
                    .$application
                        ->tuitionPost
                        ->title
                    .' has been shortlisted.',

                route(
                    'teacher.tuitions.applications'
                ),

                'tuition_application'
            );
        }

        return back()->with(
            'success',
            'Teacher shortlisted successfully.'
        );
    }

    /**
     * Accept an application and create the final assignment.
     */
    public function accept(
        TuitionPost $tuition,
        TuitionApplication $application
    ): RedirectResponse {
        $this->authorizeTuitionOwnership(
            $tuition
        );

        try {
            $result = DB::transaction(
                function () use (
                    $tuition,
                    $application
                ) {
                    /*
                    |--------------------------------------------------------------------------
                    | Lock Tuition First
                    |--------------------------------------------------------------------------
                    |
                    | Keeping one consistent lock order helps reduce deadlock risk.
                    |
                    */

                    $lockedTuition =
                        TuitionPost::query()
                            ->lockForUpdate()
                            ->findOrFail(
                                $tuition->id
                            );

                    $this->authorizeLockedTuition(
                        $lockedTuition
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Tuition Status
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedTuition
                            ->status ===
                        'filled'
                    ) {
                        return [
                            'success' => false,

                            'message' =>
                                'A teacher has already been assigned to this tuition.',
                        ];
                    }

                    if (
                        $lockedTuition
                            ->status ===
                        'closed'
                    ) {
                        return [
                            'success' => false,

                            'message' =>
                                'This tuition has already been closed.',
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Only Published Tuition Can Be Assigned
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedTuition
                            ->status !==
                        'published'
                    ) {
                        return [
                            'success' => false,

                            'message' =>
                                'This tuition is not currently available for teacher assignment.',
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Selected Application
                    |--------------------------------------------------------------------------
                    */

                    $lockedApplication =
                        TuitionApplication::query()
                            ->lockForUpdate()
                            ->findOrFail(
                                $application->id
                            );

                    /*
                    |--------------------------------------------------------------------------
                    | Application Belongs to Tuition
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedApplication
                            ->tuition_post_id !==
                        $lockedTuition->id
                    ) {
                        abort(404);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Application Still Acceptable
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! in_array(
                            $lockedApplication
                                ->status,
                            [
                                'pending',
                                'shortlisted',
                            ],
                            true
                        )
                    ) {
                        return [
                            'success' => false,

                            'message' =>
                                'This application can no longer be accepted.',
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Lock / Validate Teacher
                    |--------------------------------------------------------------------------
                    */

                    $teacherProfile =
                        TeacherProfile::query()
                            ->with('user')
                            ->lockForUpdate()
                            ->find(
                                $lockedApplication
                                    ->teacher_profile_id
                            );

                    if (! $teacherProfile) {
                        return [
                            'success' => false,

                            'message' =>
                                'The selected teacher profile no longer exists.',
                        ];
                    }

                    if (
                        ! $teacherProfile
                            ->is_verified
                    ) {
                        return [
                            'success' => false,

                            'message' =>
                                'The selected teacher is no longer verified.',
                        ];
                    }

                    if (
                        ! $teacherProfile
                            ->is_available
                    ) {
                        return [
                            'success' => false,

                            'message' =>
                                'The selected teacher is currently unavailable.',
                        ];
                    }

                    if (
                        ! $teacherProfile->user ||
                        $teacherProfile
                            ->user
                            ->status !==
                        'active'
                    ) {
                        return [
                            'success' => false,

                            'message' =>
                                'The selected teacher account is not currently active.',
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Existing Assignment Protection
                    |--------------------------------------------------------------------------
                    */

                    $existingAssignment =
                        TuitionAssignment::query()
                            ->where(
                                'tuition_post_id',
                                $lockedTuition->id
                            )
                            ->lockForUpdate()
                            ->first();

                    if ($existingAssignment) {
                        return [
                            'success' => false,

                            'message' =>
                                'A final teacher assignment already exists for this tuition.',
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Competing Applications
                    |--------------------------------------------------------------------------
                    */

                    $otherApplications =
                        TuitionApplication::query()
                            ->where(
                                'tuition_post_id',
                                $lockedTuition->id
                            )
                            ->where(
                                'id',
                                '!=',
                                $lockedApplication->id
                            )
                            ->whereIn(
                                'status',
                                [
                                    'pending',
                                    'shortlisted',
                                ]
                            )
                            ->orderBy('id')
                            ->lockForUpdate()
                            ->get();

                    /*
                    |--------------------------------------------------------------------------
                    | Accept Selected Application
                    |--------------------------------------------------------------------------
                    */

                    $lockedApplication
                        ->update([
                            'status' =>
                                'accepted',
                        ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Create Permanent Assignment
                    |--------------------------------------------------------------------------
                    */

                    $assignment =
                        TuitionAssignment::create([
                            'tuition_post_id' =>
                                $lockedTuition->id,

                            'teacher_profile_id' =>
                                $lockedApplication
                                    ->teacher_profile_id,

                            'student_user_id' =>
                                $lockedTuition
                                    ->user_id,

                            'source' =>
                                'application',

                            'tuition_application_id' =>
                                $lockedApplication
                                    ->id,

                            'teacher_request_id' =>
                                null,

                            'status' =>
                                'active',

                            'assigned_at' =>
                                now(),
                        ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Reject Competing Applications
                    |--------------------------------------------------------------------------
                    */

                    $rejectedApplicationIds =
                        [];

                    foreach (
                        $otherApplications
                        as $otherApplication
                    ) {
                        $otherApplication
                            ->update([
                                'status' =>
                                    'rejected',
                            ]);

                        $rejectedApplicationIds[] =
                            $otherApplication->id;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Mark Tuition Filled
                    |--------------------------------------------------------------------------
                    */

                    $lockedTuition
                        ->update([
                            'status' =>
                                'filled',
                        ]);

                    return [
                        'success' => true,

                        'assignment_id' =>
                            $assignment->id,

                        'accepted_application_id' =>
                            $lockedApplication->id,

                        'rejected_application_ids' =>
                            $rejectedApplicationIds,
                    ];
                }
            );
        } catch (QueryException $exception) {
            /*
            |--------------------------------------------------------------------------
            | Database Unique Guard
            |--------------------------------------------------------------------------
            */

            if (
                $this
                    ->isDuplicateAssignmentException(
                        $exception
                    )
            ) {
                return back()->with(
                    'error',
                    'A teacher has already been assigned to this tuition.'
                );
            }

            throw $exception;
        }

        /*
        |--------------------------------------------------------------------------
        | Failed Accept
        |--------------------------------------------------------------------------
        */

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Notify Accepted Teacher
        |--------------------------------------------------------------------------
        */

        $acceptedApplication =
            TuitionApplication::query()
                ->with([
                    'teacherProfile.user',
                    'tuitionPost',
                ])
                ->find(
                    $result[
                        'accepted_application_id'
                    ]
                );

        if (
            $acceptedApplication
                ?->teacherProfile
                ?->user
        ) {
            UserNotificationService::send(
                $acceptedApplication
                    ->teacherProfile
                    ->user,

                'Tuition Assigned to You',

                'Congratulations! You have been selected for '
                    .$acceptedApplication
                        ->tuitionPost
                        ->title
                    .'.',

                route(
                    'teacher.tuitions.applications'
                ),

                'tuition_assignment'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Notify Rejected Teachers
        |--------------------------------------------------------------------------
        */

        if (
            ! empty(
                $result[
                    'rejected_application_ids'
                ]
            )
        ) {
            $rejectedApplications =
                TuitionApplication::query()
                    ->with([
                        'teacherProfile.user',
                        'tuitionPost',
                    ])
                    ->whereIn(
                        'id',
                        $result[
                            'rejected_application_ids'
                        ]
                    )
                    ->get();

            foreach (
                $rejectedApplications
                as $rejectedApplication
            ) {
                $teacherUser =
                    $rejectedApplication
                        ->teacherProfile
                        ?->user;

                if (! $teacherUser) {
                    continue;
                }

                UserNotificationService::send(
                    $teacherUser,

                    'Application Status Updated',

                    'Another teacher was selected for '
                        .$rejectedApplication
                            ->tuitionPost
                            ->title
                        .'.',

                    route(
                        'teacher.tuitions.applications'
                    ),

                    'tuition_application'
                );
            }
        }

        return redirect()
            ->route(
                'student.tuitions.applications.index',
                $tuition
            )
            ->with(
                'success',
                'Teacher accepted and final tuition assignment created successfully.'
            );
    }

    /**
     * Reject an application.
     */
    public function reject(
        TuitionPost $tuition,
        TuitionApplication $application
    ): RedirectResponse {
        $this->authorizeTuitionOwnership(
            $tuition
        );

        $result = DB::transaction(
            function () use (
                $tuition,
                $application
            ) {
                /*
                |--------------------------------------------------------------------------
                | Lock Tuition
                |--------------------------------------------------------------------------
                */

                $lockedTuition =
                    TuitionPost::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $tuition->id
                        );

                $this->authorizeLockedTuition(
                    $lockedTuition
                );

                /*
                |--------------------------------------------------------------------------
                | Tuition Must Accept Changes
                |--------------------------------------------------------------------------
                */

                if (
                    in_array(
                        $lockedTuition
                            ->status,
                        [
                            'filled',
                            'closed',
                        ],
                        true
                    )
                ) {
                    return [
                        'success' => false,

                        'message' =>
                            'This tuition is no longer accepting application changes.',
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Lock Application
                |--------------------------------------------------------------------------
                */

                $lockedApplication =
                    TuitionApplication::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $application->id
                        );

                /*
                |--------------------------------------------------------------------------
                | Application Belongs to Tuition
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedApplication
                        ->tuition_post_id !==
                    $lockedTuition->id
                ) {
                    abort(404);
                }

                /*
                |--------------------------------------------------------------------------
                | Status Check
                |--------------------------------------------------------------------------
                */

                if (
                    ! in_array(
                        $lockedApplication
                            ->status,
                        [
                            'pending',
                            'shortlisted',
                        ],
                        true
                    )
                ) {
                    return [
                        'success' => false,

                        'message' =>
                            'This application cannot be rejected.',
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Reject
                |--------------------------------------------------------------------------
                */

                $lockedApplication
                    ->update([
                        'status' =>
                            'rejected',
                    ]);

                return [
                    'success' => true,

                    'application_id' =>
                        $lockedApplication->id,
                ];
            }
        );

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Notify Teacher After Commit
        |--------------------------------------------------------------------------
        */

        $application =
            TuitionApplication::query()
                ->with([
                    'teacherProfile.user',
                    'tuitionPost',
                ])
                ->find(
                    $result[
                        'application_id'
                    ]
                );

        if (
            $application
                ?->teacherProfile
                ?->user
        ) {
            UserNotificationService::send(
                $application
                    ->teacherProfile
                    ->user,

                'Application Rejected',

                'Your application for '
                    .$application
                        ->tuitionPost
                        ->title
                    .' was not selected.',

                route(
                    'teacher.tuitions.applications'
                ),

                'tuition_application'
            );
        }

        return back()->with(
            'success',
            'Application rejected successfully.'
        );
    }

    /**
     * Only the student who owns the tuition may manage applications.
     */
    private function authorizeTuitionOwnership(
        TuitionPost $tuition
    ): void {
        abort_unless(
            $tuition->user_id ===
                auth()->id(),
            403
        );
    }

    /**
     * Re-check ownership using a locked tuition row.
     */
    private function authorizeLockedTuition(
        TuitionPost $tuition
    ): void {
        abort_unless(
            $tuition->user_id ===
                auth()->id(),
            403
        );
    }

    /**
     * Check whether the applicant is still eligible.
     */
    private function checkTeacherEligibility(
        int $teacherProfileId
    ): array {
        $teacherProfile =
            TeacherProfile::query()
                ->with('user')
                ->lockForUpdate()
                ->find(
                    $teacherProfileId
                );

        if (! $teacherProfile) {
            return [
                'eligible' => false,

                'message' =>
                    'The teacher profile no longer exists.',
            ];
        }

        if (
            ! $teacherProfile
                ->is_verified
        ) {
            return [
                'eligible' => false,

                'message' =>
                    'This teacher is no longer verified.',
            ];
        }

        if (
            ! $teacherProfile
                ->is_available
        ) {
            return [
                'eligible' => false,

                'message' =>
                    'This teacher is currently unavailable.',
            ];
        }

        if (
            ! $teacherProfile->user ||
            $teacherProfile
                ->user
                ->status !==
            'active'
        ) {
            return [
                'eligible' => false,

                'message' =>
                    'This teacher account is not currently active.',
            ];
        }

        return [
            'eligible' => true,
            'message' => null,
        ];
    }

    /**
     * Determine whether a DB exception means the tuition already
     * has a final assignment.
     */
    private function isDuplicateAssignmentException(
        QueryException $exception
    ): bool {
        $mysqlErrorCode =
            $exception->errorInfo[1]
            ?? null;

        if (
            (int) $mysqlErrorCode !==
            1062
        ) {
            return false;
        }

        $message =
            strtolower(
                $exception
                    ->getMessage()
            );

        /*
        |--------------------------------------------------------------------------
        | Accept Any Duplicate Collision on tuition_assignments
        |--------------------------------------------------------------------------
        |
        | This avoids depending on the exact index name generated by an older
        | migration.
        |
        */

        return str_contains(
            $message,
            'tuition_assignments'
        )
            ||
            str_contains(
                $message,
                'tuition_post_id'
            );
    }
}