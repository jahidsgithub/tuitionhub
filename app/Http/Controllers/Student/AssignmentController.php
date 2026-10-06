<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TuitionAssignment;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(): View
    {
        $assignments = TuitionAssignment::query()
            ->with([
                'tuitionPost.location',
                'tuitionPost.subjects',
                'teacherProfile.user',
                'review',
            ])
            ->where(
                'student_user_id',
                auth()->id()
            )
            ->latest('assigned_at')
            ->paginate(15);

        return view(
            'student.assignments.index',
            compact('assignments')
        );
    }

    public function complete(
        TuitionAssignment $assignment
    ): RedirectResponse {
        $this->authorizeAssignment(
            $assignment
        );

        $result = DB::transaction(
            function () use ($assignment) {
                $lockedAssignment =
                    TuitionAssignment::query()
                        ->with([
                            'teacherProfile.user',
                            'tuitionPost',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $assignment->id
                        );

                if (
                    $lockedAssignment->student_user_id !==
                    auth()->id()
                ) {
                    abort(403);
                }

                if (
                    $lockedAssignment->status !==
                    'active'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'Only an active assignment can be completed.',
                    ];
                }

                $lockedAssignment->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'cancelled_at' => null,
                ]);

                return [
                    'success' => true,
                    'assignment_id' =>
                        $lockedAssignment->id,
                ];
            }
        );

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        $assignment =
            TuitionAssignment::query()
                ->with([
                    'teacherProfile.user',
                    'tuitionPost',
                ])
                ->find(
                    $result['assignment_id']
                );

        if (
            $assignment
                ?->teacherProfile
                ?->user
        ) {
            UserNotificationService::send(
                $assignment
                    ->teacherProfile
                    ->user,
                'Tuition Assignment Completed',
                'The student marked '
                    .$assignment
                        ->tuitionPost
                        ->title
                    .' as completed.',
                route(
                    'teacher.assignments.index'
                ),
                'tuition_assignment'
            );
        }

        return back()->with(
            'success',
            'Tuition assignment marked as completed. You can now rate your teacher.'
        );
    }

    public function cancel(
        TuitionAssignment $assignment
    ): RedirectResponse {
        $this->authorizeAssignment(
            $assignment
        );

        $result = DB::transaction(
            function () use ($assignment) {
                $lockedAssignment =
                    TuitionAssignment::query()
                        ->with([
                            'teacherProfile.user',
                            'tuitionPost',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $assignment->id
                        );

                if (
                    $lockedAssignment->student_user_id !==
                    auth()->id()
                ) {
                    abort(403);
                }

                if (
                    $lockedAssignment->status !==
                    'active'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'Only an active assignment can be cancelled.',
                    ];
                }

                $lockedTuition =
                    $lockedAssignment
                        ->tuitionPost()
                        ->lockForUpdate()
                        ->firstOrFail();

                $lockedAssignment->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'completed_at' => null,
                ]);

                $lockedTuition->update([
                    'status' => 'closed',
                ]);

                return [
                    'success' => true,
                    'assignment_id' =>
                        $lockedAssignment->id,
                ];
            }
        );

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        $assignment =
            TuitionAssignment::query()
                ->with([
                    'teacherProfile.user',
                    'tuitionPost',
                ])
                ->find(
                    $result['assignment_id']
                );

        if (
            $assignment
                ?->teacherProfile
                ?->user
        ) {
            UserNotificationService::send(
                $assignment
                    ->teacherProfile
                    ->user,
                'Tuition Assignment Cancelled',
                'The student cancelled the assignment for '
                    .$assignment
                        ->tuitionPost
                        ->title
                    .'.',
                route(
                    'teacher.assignments.index'
                ),
                'tuition_assignment'
            );
        }

        return back()->with(
            'success',
            'Tuition assignment cancelled successfully.'
        );
    }

    private function authorizeAssignment(
        TuitionAssignment $assignment
    ): void {
        abort_unless(
            $assignment->student_user_id ===
            auth()->id(),
            403
        );
    }
}