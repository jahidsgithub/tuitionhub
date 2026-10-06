<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TuitionAssignment;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    /**
     * Teacher's final tuition assignments.
     */
    public function index(): View
    {
        $profile = auth()
            ->user()
            ->teacherProfile;

        if (! $profile) {
            return view(
                'teacher.assignments.index',
                [
                    'assignments' => collect(),
                ]
            );
        }

        $assignments = TuitionAssignment::query()
            ->with([
                'tuitionPost.location',
                'tuitionPost.subjects',
                'student.studentProfile',
            ])
            ->where(
                'teacher_profile_id',
                $profile->id
            )
            ->latest('assigned_at')
            ->paginate(15);

        return view(
            'teacher.assignments.index',
            compact('assignments')
        );
    }
}