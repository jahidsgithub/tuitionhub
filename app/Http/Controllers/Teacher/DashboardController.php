<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherRequest;
use App\Models\TuitionPost;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $teacherProfile = $user->teacherProfile;

        $activeSubscription = $user->activeSubscription();

        $recommendedCount = 0;
        $applicationsCount = 0;
        $shortlistedCount = 0;
        $acceptedCount = 0;
        $incomingRequestCount = 0;

        if ($teacherProfile) {

            $applicationsCount = $teacherProfile
                ->applications()
                ->count();

            $shortlistedCount = $teacherProfile
                ->applications()
                ->where('status', 'shortlisted')
                ->count();

            $acceptedCount = $teacherProfile
                ->applications()
                ->where('status', 'accepted')
                ->count();

            $incomingRequestCount = TeacherRequest::where(
                'teacher_profile_id',
                $teacherProfile->id
            )
                ->where(
                    'status',
                    'pending'
                )
                ->count();

            $recommendedQuery = TuitionPost::query()
                ->where('status', 'published');

            if ($teacherProfile->gender) {
                $recommendedQuery->where(
                    function ($query) use ($teacherProfile) {
                        $query
                            ->where(
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

            $teacherSubjectIds = $teacherProfile
                ->subjects()
                ->pluck('subjects.id');

            if ($teacherSubjectIds->isNotEmpty()) {
                $recommendedQuery->whereHas(
                    'subjects',
                    function ($query) use ($teacherSubjectIds) {
                        $query->whereIn(
                            'subjects.id',
                            $teacherSubjectIds
                        );
                    }
                );
            }

            $teacherLocationIds = $teacherProfile
                ->locations()
                ->pluck('locations.id');

            if ($teacherLocationIds->isNotEmpty()) {
                $recommendedQuery->where(
                    function ($query) use ($teacherLocationIds) {
                        $query
                            ->whereIn(
                                'location_id',
                                $teacherLocationIds
                            )
                            ->orWhereNull('location_id');
                    }
                );
            }

            $recommendedCount = $recommendedQuery->count();
        }

        $recentTuitions = TuitionPost::with([
            'location',
            'subjects',
        ])
            ->where(
                'status',
                'published'
            )
            ->latest('published_at')
            ->take(5)
            ->get();

        return view(
            'teacher.dashboard',
            compact(
                'user',
                'teacherProfile',
                'activeSubscription',
                'recommendedCount',
                'applicationsCount',
                'shortlistedCount',
                'acceptedCount',
                'incomingRequestCount',
                'recentTuitions'
            )
        );
    }
}