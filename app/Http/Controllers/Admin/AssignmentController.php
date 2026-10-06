<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TuitionAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    /**
     * Show all final tuition assignments.
     */
    public function index(
        Request $request
    ): View {
        $query = TuitionAssignment::query()
            ->with([
                'tuitionPost.location',
                'tuitionPost.subjects',
                'teacherProfile.user',
                'student.studentProfile',
                'tuitionApplication',
                'teacherRequest',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim(
                $request->search
            );

            $query->where(function ($q) use ($search) {
                $q->whereHas(
                    'tuitionPost',
                    function ($tuitionQuery) use ($search) {
                        $tuitionQuery
                            ->where(
                                'tuition_code',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'title',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'class_level',
                                'like',
                                "%{$search}%"
                            );
                    }
                )
                    ->orWhereHas(
                        'teacherProfile.user',
                        function ($teacherQuery) use ($search) {
                            $teacherQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'phone',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    )
                    ->orWhereHas(
                        'student',
                        function ($studentQuery) use ($search) {
                            $studentQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'phone',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Source Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('source')) {
            $query->where(
                'source',
                $request->source
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalAssignments = TuitionAssignment::count();

        $activeAssignments = TuitionAssignment::query()
            ->where(
                'status',
                'active'
            )
            ->count();

        $completedAssignments = TuitionAssignment::query()
            ->where(
                'status',
                'completed'
            )
            ->count();

        $cancelledAssignments = TuitionAssignment::query()
            ->where(
                'status',
                'cancelled'
            )
            ->count();

        $applicationAssignments = TuitionAssignment::query()
            ->where(
                'source',
                'application'
            )
            ->count();

        $directAssignments = TuitionAssignment::query()
            ->where(
                'source',
                'direct_request'
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Completion Rate
        |--------------------------------------------------------------------------
        */

        $finishedAssignments =
            $completedAssignments +
            $cancelledAssignments;

        $completionRate = $finishedAssignments > 0
            ? round(
                (
                    $completedAssignments /
                    $finishedAssignments
                ) * 100,
                1
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Results
        |--------------------------------------------------------------------------
        */

        $assignments = $query
            ->latest('assigned_at')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.assignments.index',
            compact(
                'assignments',
                'totalAssignments',
                'activeAssignments',
                'completedAssignments',
                'cancelledAssignments',
                'applicationAssignments',
                'directAssignments',
                'completionRate'
            )
        );
    }
}