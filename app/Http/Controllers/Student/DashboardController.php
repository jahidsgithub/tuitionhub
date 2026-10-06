<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TuitionApplication;
use App\Models\TuitionPost;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $activeTuitionCount = TuitionPost::where(
            'user_id',
            $user->id
        )
            ->where('status', 'published')
            ->count();

        $filledTuitionCount = TuitionPost::where(
            'user_id',
            $user->id
        )
            ->where('status', 'filled')
            ->count();

        $applicationsCount = TuitionApplication::whereHas(
            'tuitionPost',
            function ($query) use ($user) {
                $query->where(
                    'user_id',
                    $user->id
                );
            }
        )->count();

        $shortlistedCount = TuitionApplication::whereHas(
            'tuitionPost',
            function ($query) use ($user) {
                $query->where(
                    'user_id',
                    $user->id
                );
            }
        )
            ->where('status', 'shortlisted')
            ->count();

        $recentTuitions = TuitionPost::with([
            'location',
            'subjects',
        ])
            ->withCount('applications')
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view(
            'student.dashboard',
            compact(
                'user',
                'activeTuitionCount',
                'filledTuitionCount',
                'applicationsCount',
                'shortlistedCount',
                'recentTuitions'
            )
        );
    }
}