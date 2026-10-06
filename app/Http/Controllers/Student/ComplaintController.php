<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\TuitionAssignment;
use App\Services\AdminNotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function index(): View
    {
        $complaints = Complaint::query()
            ->with([
                'assignment.tuitionPost',
                'reportedUser',
            ])
            ->where(
                'reported_by',
                auth()->id()
            )
            ->latest()
            ->paginate(15);

        return view(
            'student.complaints.index',
            compact('complaints')
        );
    }

    public function create(
        TuitionAssignment $assignment
    ): View {
        abort_unless(
            $assignment
                ->student_user_id ===
                auth()->id(),
            403
        );

        $assignment->load([
            'teacherProfile.user',
            'tuitionPost',
        ]);

        return view(
            'student.complaints.create',
            compact('assignment')
        );
    }

    public function store(
        Request $request,
        TuitionAssignment $assignment
    ): RedirectResponse {
        abort_unless(
            $assignment
                ->student_user_id ===
                auth()->id(),
            403
        );

        $validated =
            $request->validate([
                'category' => [
                    'required',
                    'in:behavior,payment,attendance,misinformation,harassment,safety,other',
                ],

                'subject' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'description' => [
                    'required',
                    'string',
                    'max:5000',
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
                            ->with(
                                'teacherProfile.user'
                            )
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

                    $reportedUser =
                        $lockedAssignment
                            ->teacherProfile
                            ?->user;

                    if (! $reportedUser) {
                        return [
                            'success' =>
                                false,

                            'message' =>
                                'The assigned teacher account could not be found.',
                        ];
                    }

                    $existingComplaint =
                        Complaint::query()
                            ->where(
                                'tuition_assignment_id',
                                $lockedAssignment->id
                            )
                            ->where(
                                'reported_by',
                                auth()->id()
                            )
                            ->whereIn(
                                'status',
                                [
                                    'open',
                                    'investigating',
                                ]
                            )
                            ->lockForUpdate()
                            ->first();

                    if (
                        $existingComplaint
                    ) {
                        return [
                            'success' =>
                                false,

                            'message' =>
                                'You already have an unresolved complaint for this assignment.',
                        ];
                    }

                    $complaint =
                        Complaint::create([
                            'tuition_assignment_id' =>
                                $lockedAssignment->id,

                            'reported_by' =>
                                auth()->id(),

                            'reported_user_id' =>
                                $reportedUser->id,

                            'category' =>
                                $validated[
                                    'category'
                                ],

                            'subject' =>
                                $validated[
                                    'subject'
                                ],

                            'description' =>
                                $validated[
                                    'description'
                                ],

                            'status' =>
                                'open',
                        ]);

                    return [
                        'success' =>
                            true,

                        'complaint_id' =>
                            $complaint->id,
                    ];
                }
            );
        } catch (QueryException $exception) {
            if (
                $this
                    ->isDuplicateUnresolvedComplaint(
                        $exception
                    )
            ) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'You already have an unresolved complaint for this assignment.'
                    );
            }

            throw $exception;
        }

        if (! $result['success']) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    $result['message']
                );
        }

        AdminNotificationService::send(
            'New Student Complaint',

            auth()->user()->name
            .' submitted complaint #'
            .$result[
                'complaint_id'
            ]
            .'.',

            route(
                'admin.complaints.index',
                [
                    'status' =>
                        'open',

                    'search' =>
                        $result[
                            'complaint_id'
                        ],
                ]
            ),

            'complaint'
        );

        return redirect()
            ->route(
                'student.complaints.index'
            )
            ->with(
                'success',
                'Complaint submitted successfully.'
            );
    }

    private function isDuplicateUnresolvedComplaint(
        QueryException $exception
    ): bool {
        $mysqlCode =
            $exception->errorInfo[1]
            ?? null;

        if (
            (int) $mysqlCode !==
            1062
        ) {
            return false;
        }

        return str_contains(
            strtolower(
                $exception
                    ->getMessage()
            ),
            'complaints_unresolved_unique'
        );
    }
}