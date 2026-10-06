<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherProfile;
use App\Models\TuitionApplication;
use App\Models\TuitionPost;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalTeachers = User::where('role', 'teacher')->count();

        $totalStudents = User::where('role', 'student')->count();

        $verifiedTeachers = TeacherProfile::where(
            'is_verified',
            true
        )->count();

        $pendingTeachers = TeacherProfile::where(
            'is_verified',
            false
        )->count();

        $publishedTuitions = TuitionPost::where(
            'status',
            'published'
        )->count();

        $filledTuitions = TuitionPost::where(
            'status',
            'filled'
        )->count();

        $totalApplications = TuitionApplication::count();

        $recentTeachers = TeacherProfile::with('user')
            ->latest()
            ->take(5)
            ->get();

        $recentTuitions = TuitionPost::with([
            'user',
            'location',
            'subjects',
        ])
            ->latest()
            ->take(5)
            ->get();

        return view(
            'admin.dashboard',
            compact(
                'totalTeachers',
                'totalStudents',
                'verifiedTeachers',
                'pendingTeachers',
                'publishedTuitions',
                'filledTuitions',
                'totalApplications',
                'recentTeachers',
                'recentTuitions'
            )
        );
    }
}