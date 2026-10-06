<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\TuitionApplication;
use App\Models\TuitionPost;
use App\Services\UserNotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TuitionController extends Controller
{
    /**
     * Browse published tuition posts.
     */
    public function index(
        Request $request
    ): View|RedirectResponse {
        $user = $request->user();

        $teacherProfile =
            $user->teacherProfile;

        /*
        |--------------------------------------------------------------------------
        | Teacher Profile Required
        |--------------------------------------------------------------------------
        */

        if (! $teacherProfile) {
            return redirect()
                ->route(
                    'teacher.profile.edit'
                )
                ->with(
                    'error',
                    'Please complete your teacher profile first.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Verification Required
        |--------------------------------------------------------------------------
        */

        if (! $teacherProfile->is_verified) {
            return redirect()
                ->route(
                    'teacher.profile.edit'
                )
                ->with(
                    'error',
                    'Your teacher profile is waiting for admin verification. You can browse and apply for tuition after verification.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Availability Required
        |--------------------------------------------------------------------------
        */

        if (! $teacherProfile->is_available) {
            return redirect()
                ->route(
                    'teacher.profile.edit'
                )
                ->with(
                    'error',
                    'Your profile is currently marked unavailable. Enable availability before browsing tuition opportunities.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Published Tuition Query
        |--------------------------------------------------------------------------
        */

        $query = TuitionPost::query()
            ->with([
                'location',
                'subjects',
                'user',
            ])
            ->where(
                'status',
                'published'
            );

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim(
                (string) $request->search
            );

            $query->where(
                function ($q) use ($search) {
                    $q->where(
                        'title',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhere(
                            'class_level',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'tuition_code',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'subjects',
                            function (
                                $subjectQuery
                            ) use ($search) {
                                $subjectQuery->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                );
                            }
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Location Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('location')) {
            $query->where(
                'location_id',
                $request->location
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Teaching Mode Filter
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'teaching_mode'
            )
        ) {
            $query->where(
                'teaching_mode',
                $request->teaching_mode
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Gender Match
        |--------------------------------------------------------------------------
        */

        if ($teacherProfile->gender) {
            $query->where(
                function (
                    $q
                ) use ($teacherProfile) {
                    $q->where(
                        'preferred_teacher_gender',
                        'any'
                    )
                        ->orWhere(
                            'preferred_teacher_gender',
                            $teacherProfile->gender
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Subject Match Priority
        |--------------------------------------------------------------------------
        */

        $teacherSubjectIds =
            $teacherProfile
                ->subjects()
                ->pluck(
                    'subjects.id'
                );

        if (
            $teacherSubjectIds
                ->isNotEmpty()
        ) {
            $query->withCount([
                'subjects as matching_subjects_count' =>
                    function (
                        $subjectQuery
                    ) use (
                        $teacherSubjectIds
                    ) {
                        $subjectQuery
                            ->whereIn(
                                'subjects.id',
                                $teacherSubjectIds
                            );
                    },
            ]);

            $query->orderByDesc(
                'matching_subjects_count'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $tuitions = $query
            ->orderByDesc(
                'published_at'
            )
            ->paginate(12)
            ->withQueryString();

        return view(
            'teacher.tuitions.index',
            compact('tuitions')
        );
    }

    /**
     * Show one tuition post.
     */
    public function show(
        TuitionPost $tuition
    ): View|RedirectResponse {
        $user = auth()->user();

        $teacherProfile =
            $user->teacherProfile;

        /*
        |--------------------------------------------------------------------------
        | Verified Teacher Required
        |--------------------------------------------------------------------------
        */

        if (
            ! $teacherProfile ||
            ! $teacherProfile->is_verified
        ) {
            return redirect()
                ->route(
                    'teacher.profile.edit'
                )
                ->with(
                    'error',
                    'Your teacher profile must be verified before viewing tuition details.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Availability Required
        |--------------------------------------------------------------------------
        */

        if (
            ! $teacherProfile
                ->is_available
        ) {
            return redirect()
                ->route(
                    'teacher.profile.edit'
                )
                ->with(
                    'error',
                    'Your profile is currently unavailable.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Published Tuition Only
        |--------------------------------------------------------------------------
        */

        abort_if(
            $tuition->status !==
                'published',
            404
        );

        $tuition->load([
            'location',
            'subjects',
            'user',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Active Subscription
        |--------------------------------------------------------------------------
        */

        $activeSubscription =
            $user
                ->activeSubscription();

        /*
        |--------------------------------------------------------------------------
        | Existing Application
        |--------------------------------------------------------------------------
        */

        $alreadyApplied =
            TuitionApplication::query()
                ->where(
                    'tuition_post_id',
                    $tuition->id
                )
                ->where(
                    'teacher_profile_id',
                    $teacherProfile->id
                )
                ->exists();

        return view(
            'teacher.tuitions.show',
            compact(
                'tuition',
                'alreadyApplied',
                'activeSubscription'
            )
        );
    }

    /**
     * Apply for tuition safely.
     */
    public function apply(
        Request $request,
        TuitionPost $tuition
    ): RedirectResponse {
        $user = $request->user();

        $teacherProfile =
            $user->teacherProfile;

        /*
        |--------------------------------------------------------------------------
        | Teacher Profile Required
        |--------------------------------------------------------------------------
        */

        if (! $teacherProfile) {
            return redirect()
                ->route(
                    'teacher.profile.edit'
                )
                ->with(
                    'error',
                    'Please complete your teacher profile first.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Verification Required
        |--------------------------------------------------------------------------
        */

        if (
            ! $teacherProfile
                ->is_verified
        ) {
            return redirect()
                ->route(
                    'teacher.profile.edit'
                )
                ->with(
                    'error',
                    'Your teacher profile must be verified by the admin before you can apply for tuition.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Availability Required
        |--------------------------------------------------------------------------
        */

        if (
            ! $teacherProfile
                ->is_available
        ) {
            return redirect()
                ->route(
                    'teacher.profile.edit'
                )
                ->with(
                    'error',
                    'Your profile is currently marked unavailable. Enable availability before applying.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Input
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'message' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'expected_salary' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999',
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | Atomic Apply Transaction
        |--------------------------------------------------------------------------
        */

        try {
            $result = DB::transaction(
                function () use (
                    $user,
                    $teacherProfile,
                    $tuition,
                    $validated
                ) {
                    /*
                    |--------------------------------------------------------------------------
                    | Lock Teacher Profile
                    |--------------------------------------------------------------------------
                    */

                    $lockedTeacherProfile =
                        $teacherProfile
                            ->newQuery()
                            ->lockForUpdate()
                            ->findOrFail(
                                $teacherProfile->id
                            );

                    /*
                    |--------------------------------------------------------------------------
                    | Re-check Verification
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! $lockedTeacherProfile
                            ->is_verified
                    ) {
                        return [
                            'success' =>
                                false,

                            'message' =>
                                'Your teacher verification is no longer active.',

                            'verification_required' =>
                                true,
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Re-check Availability
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! $lockedTeacherProfile
                            ->is_available
                    ) {
                        return [
                            'success' =>
                                false,

                            'message' =>
                                'Your teacher profile is currently unavailable.',

                            'verification_required' =>
                                true,
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Tuition
                    |--------------------------------------------------------------------------
                    */

                    $lockedTuition =
                        TuitionPost::query()
                            ->with('user')
                            ->lockForUpdate()
                            ->findOrFail(
                                $tuition->id
                            );

                    /*
                    |--------------------------------------------------------------------------
                    | Re-check Tuition Status
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedTuition
                            ->status !==
                        'published'
                    ) {
                        return [
                            'success' =>
                                false,

                            'message' =>
                                'This tuition is no longer available.',
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Duplicate Application Check
                    |--------------------------------------------------------------------------
                    */

                    $existingApplication =
                        TuitionApplication::query()
                            ->where(
                                'tuition_post_id',
                                $lockedTuition->id
                            )
                            ->where(
                                'teacher_profile_id',
                                $lockedTeacherProfile->id
                            )
                            ->exists();

                    if (
                        $existingApplication
                    ) {
                        return [
                            'success' =>
                                false,

                            'message' =>
                                'You have already applied for this tuition.',
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Active Subscription
                    |--------------------------------------------------------------------------
                    */

                    $subscription =
                        Subscription::query()
                            ->where(
                                'user_id',
                                $user->id
                            )
                            ->where(
                                'status',
                                'active'
                            )
                            ->where(
                                function (
                                    $query
                                ) {
                                    $query
                                        ->whereNull(
                                            'expires_at'
                                        )
                                        ->orWhere(
                                            'expires_at',
                                            '>',
                                            now()
                                        );
                                }
                            )
                            ->orderByDesc(
                                'expires_at'
                            )
                            ->lockForUpdate()
                            ->first();

                    /*
                    |--------------------------------------------------------------------------
                    | Active Subscription Required
                    |--------------------------------------------------------------------------
                    */

                    if (! $subscription) {
                        return [
                            'success' =>
                                false,

                            'message' =>
                                'You need an active subscription before applying for tuition.',

                            'subscription_required' =>
                                true,
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Re-check Expiry
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! $subscription
                            ->isActive()
                    ) {
                        return [
                            'success' =>
                                false,

                            'message' =>
                                'Your subscription has expired.',

                            'subscription_required' =>
                                true,
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Snapshot Application Limit
                    |--------------------------------------------------------------------------
                    */

                    $applicationLimit =
                        $subscription
                            ->snapshotApplicationLimit();

                    /*
                    |--------------------------------------------------------------------------
                    | Application Limit Check
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $applicationLimit !==
                            null &&
                        $subscription
                            ->applications_used >=
                            $applicationLimit
                    ) {
                        return [
                            'success' =>
                                false,

                            'message' =>
                                'You have reached the application limit of your current subscription plan.',

                            'subscription_required' =>
                                true,
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Create Application
                    |--------------------------------------------------------------------------
                    */

                    $application =
                        TuitionApplication::create([
                            'tuition_post_id' =>
                                $lockedTuition->id,

                            'teacher_profile_id' =>
                                $lockedTeacherProfile->id,

                            'message' =>
                                $validated[
                                    'message'
                                ] ?? null,

                            'expected_salary' =>
                                $validated[
                                    'expected_salary'
                                ] ?? null,

                            'status' =>
                                'pending',
                        ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Consume Application Credit
                    |--------------------------------------------------------------------------
                    |
                    | If the INSERT above fails because of the unique DB guard,
                    | the transaction rolls back and this increment never persists.
                    |
                    */

                    $subscription
                        ->increment(
                            'applications_used'
                        );

                    $subscription
                        ->refresh();

                    return [
                        'success' =>
                            true,

                        'application' =>
                            $application,

                        'tuition' =>
                            $lockedTuition,

                        'applications_used' =>
                            $subscription
                                ->applications_used,

                        'application_limit' =>
                            $applicationLimit,
                    ];
                }
            );
        } catch (QueryException $exception) {
            /*
            |--------------------------------------------------------------------------
            | Graceful Database Duplicate Protection
            |--------------------------------------------------------------------------
            */

            if (
                $this
                    ->isDuplicateApplicationException(
                        $exception
                    )
            ) {
                return redirect()
                    ->route(
                        'teacher.tuitions.show',
                        $tuition
                    )
                    ->with(
                        'error',
                        'You have already applied for this tuition.'
                    );
            }

            throw $exception;
        }

        /*
        |--------------------------------------------------------------------------
        | Failed Apply
        |--------------------------------------------------------------------------
        */

        if (! $result['success']) {
            if (
                $result[
                    'verification_required'
                ] ?? false
            ) {
                return redirect()
                    ->route(
                        'teacher.profile.edit'
                    )
                    ->with(
                        'error',
                        $result['message']
                    );
            }

            if (
                $result[
                    'subscription_required'
                ] ?? false
            ) {
                return redirect()
                    ->route(
                        'teacher.subscription.index'
                    )
                    ->with(
                        'error',
                        $result['message']
                    );
            }

            return back()->with(
                'error',
                $result['message']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Notify Student After Commit
        |--------------------------------------------------------------------------
        */

        $lockedTuition =
            $result['tuition'];

        if ($lockedTuition->user) {
            UserNotificationService::send(
                $lockedTuition->user,

                'New Teacher Application',

                $user->name
                    .' applied for '
                    .$lockedTuition->title
                    .'.',

                route(
                    'student.tuitions.applications.index',
                    $lockedTuition
                ),

                'tuition_application'
            );
        }

        return redirect()
            ->route(
                'teacher.tuitions.show',
                $lockedTuition
            )
            ->with(
                'success',
                'Application submitted successfully.'
            );
    }

    /**
     * Show teacher applications.
     */
    public function applications(): View
    {
        $teacherProfile =
            auth()
                ->user()
                ->teacherProfile;

        if (! $teacherProfile) {
            return view(
                'teacher.tuitions.applications',
                [
                    'applications' =>
                        collect(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Historical Applications Stay Visible
        |--------------------------------------------------------------------------
        */

        $applications =
            TuitionApplication::query()
                ->with([
                    'tuitionPost.location',
                    'tuitionPost.subjects',
                ])
                ->where(
                    'teacher_profile_id',
                    $teacherProfile->id
                )
                ->latest()
                ->paginate(10);

        return view(
            'teacher.tuitions.applications',
            compact('applications')
        );
    }

    /**
     * Withdraw application safely.
     */
    public function withdraw(
        TuitionApplication $application
    ): RedirectResponse {
        $user = auth()->user();

        $teacherProfile =
            $user->teacherProfile;

        /*
        |--------------------------------------------------------------------------
        | Teacher Profile Required
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $teacherProfile,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Atomic Withdrawal
        |--------------------------------------------------------------------------
        */

        $result = DB::transaction(
            function () use (
                $application,
                $teacherProfile
            ) {
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
                | Ownership Check
                |--------------------------------------------------------------------------
                */

                abort_unless(
                    $lockedApplication
                        ->teacher_profile_id ===
                    $teacherProfile->id,
                    403
                );

                /*
                |--------------------------------------------------------------------------
                | Withdrawable Status Check
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
                        'success' =>
                            false,

                        'message' =>
                            'This application can no longer be withdrawn.',
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Load Tuition / Student
                |--------------------------------------------------------------------------
                */

                $lockedApplication
                    ->load(
                        'tuitionPost.user'
                    );

                /*
                |--------------------------------------------------------------------------
                | Withdraw
                |--------------------------------------------------------------------------
                */

                $lockedApplication
                    ->update([
                        'status' =>
                            'withdrawn',
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Application Credit Is Not Refunded
                |--------------------------------------------------------------------------
                */

                return [
                    'success' =>
                        true,

                    'application' =>
                        $lockedApplication,
                ];
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Failed Withdrawal
        |--------------------------------------------------------------------------
        */

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        $lockedApplication =
            $result['application'];

        /*
        |--------------------------------------------------------------------------
        | Notify Student After Commit
        |--------------------------------------------------------------------------
        */

        if (
            $lockedApplication
                ->tuitionPost
                ?->user
        ) {
            UserNotificationService::send(
                $lockedApplication
                    ->tuitionPost
                    ->user,

                'Application Withdrawn',

                $user->name
                    .' withdrew an application for '
                    .$lockedApplication
                        ->tuitionPost
                        ->title
                    .'.',

                route(
                    'student.tuitions.applications.index',
                    $lockedApplication
                        ->tuitionPost
                ),

                'tuition_application'
            );
        }

        return back()->with(
            'success',
            'Application withdrawn successfully.'
        );
    }

    /**
     * Determine whether the database exception came from the
     * teacher + tuition application unique constraint.
     */
    private function isDuplicateApplicationException(
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
        | Support Current + Older Unique Index Names
        |--------------------------------------------------------------------------
        */

        return
            str_contains(
                $message,
                'tuition_application_teacher_unique'
            )
            ||
            str_contains(
                $message,
                'tuition_applications_tuition_post_id_teacher_profile_id_unique'
            )
            ||
            (
                str_contains(
                    $message,
                    'tuition_applications'
                )
                &&
                str_contains(
                    $message,
                    'tuition_post_id'
                )
            );
    }
}