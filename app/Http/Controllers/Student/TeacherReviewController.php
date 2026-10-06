<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TeacherReview;
use App\Models\TuitionAssignment;
use App\Services\UserNotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherReviewController extends Controller
{
    public function store(
        Request $request,
        TuitionAssignment $assignment
    ): RedirectResponse {
        abort_unless(
            $assignment->student_user_id ===
                auth()->id(),
            403
        );

        $validated = $request->validate([
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],

            'review' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        try {
            $result = DB::transaction(
                function () use (
                    $assignment,
                    $validated
                ) {
                    $lockedAssignment =
                        TuitionAssignment::query()
                            ->with([
                                'teacherProfile.user',
                                'review',
                            ])
                            ->lockForUpdate()
                            ->findOrFail(
                                $assignment->id
                            );

                    abort_unless(
                        $lockedAssignment
                            ->student_user_id ===
                            auth()->id(),
                        403
                    );

                    if (
                        $lockedAssignment
                            ->status !==
                            'completed'
                    ) {
                        return [
                            'success' => false,
                            'message' =>
                                'You can review a teacher only after the tuition assignment is completed.',
                        ];
                    }

                    if (
                        ! $lockedAssignment
                            ->teacherProfile
                    ) {
                        return [
                            'success' => false,
                            'message' =>
                                'The assigned teacher profile could not be found.',
                        ];
                    }

                    if (
                        $lockedAssignment
                            ->review
                    ) {
                        return [
                            'success' => false,
                            'message' =>
                                'You have already reviewed this tuition assignment.',
                        ];
                    }

                    $existingReview =
                        TeacherReview::query()
                            ->where(
                                'tuition_assignment_id',
                                $lockedAssignment->id
                            )
                            ->exists();

                    if ($existingReview) {
                        return [
                            'success' => false,
                            'message' =>
                                'You have already reviewed this tuition assignment.',
                        ];
                    }

                    $review =
                        TeacherReview::create([
                            'tuition_assignment_id' =>
                                $lockedAssignment->id,

                            'teacher_profile_id' =>
                                $lockedAssignment
                                    ->teacher_profile_id,

                            'student_user_id' =>
                                $lockedAssignment
                                    ->student_user_id,

                            'rating' =>
                                $validated[
                                    'rating'
                                ],

                            'review' =>
                                $validated[
                                    'review'
                                ] ?? null,
                        ]);

                    return [
                        'success' => true,
                        'review_id' =>
                            $review->id,
                    ];
                }
            );
        } catch (QueryException $exception) {
            if (
                $this->isDuplicateReviewException(
                    $exception
                )
            ) {
                return back()->with(
                    'error',
                    'You have already reviewed this tuition assignment.'
                );
            }

            throw $exception;
        }

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        $review = TeacherReview::query()
            ->with([
                'teacherProfile.user',
                'tuitionAssignment.tuitionPost',
            ])
            ->find(
                $result['review_id']
            );

        if (
            $review
                ?->teacherProfile
                ?->user
        ) {
            UserNotificationService::send(
                $review
                    ->teacherProfile
                    ->user,

                'New Teacher Review',

                'You received a '
                .$review->rating
                .'-star review for '
                .(
                    $review
                        ->tuitionAssignment
                        ?->tuitionPost
                        ?->title
                    ?? 'a completed tuition'
                )
                .'.',

                route(
                    'teacher.assignments.index'
                ),

                'teacher_review'
            );
        }

        return back()->with(
            'success',
            'Thank you. Your review has been submitted successfully.'
        );
    }

    private function isDuplicateReviewException(
        QueryException $exception
    ): bool {
        $mysqlCode =
            $exception->errorInfo[1]
            ?? null;

        if ((int) $mysqlCode !== 1062) {
            return false;
        }

        $message = strtolower(
            $exception->getMessage()
        );

        return str_contains(
            $message,
            'teacher_reviews_assignment_unique'
        )
            || str_contains(
                $message,
                'teacher_reviews_tuition_assignment_id_unique'
            );
    }
}