<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherProfile;
use App\Models\TeacherProfileEditRequest;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherVerificationController extends Controller
{
    public function index(
        Request $request
    ): View {
        $query = TeacherProfile::query()
            ->with([
                'user',
                'subjects',
                'locations',
            ])
            ->withCount([
                'verificationDocuments',

                'verificationDocuments as approved_documents_count' => function ($query) {
                    $query->where(
                        'status',
                        'approved'
                    );
                },

                'verificationDocuments as pending_documents_count' => function ($query) {
                    $query->where(
                        'status',
                        'pending'
                    );
                },

                'profileEditRequests as pending_profile_edit_requests_count' => function ($query) {
                    $query->where(
                        'status',
                        TeacherProfileEditRequest::STATUS_PENDING
                    );
                },
            ]);

        if ($request->filled('search')) {
            $search = trim(
                (string) $request->search
            );

            $query->whereHas(
                'user',
                function ($userQuery) use ($search) {
                    $userQuery
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
        }

        if ($request->filled('status')) {
            if (
                $request->status ===
                'verified'
            ) {
                $query->where(
                    'is_verified',
                    true
                );
            }

            if (
                $request->status ===
                'pending'
            ) {
                $query->where(
                    'is_verified',
                    false
                );
            }
        }

        $teachers =
            $query
                ->latest()
                ->paginate(15)
                ->withQueryString();

        return view(
            'admin.teachers.index',
            compact(
                'teachers'
            )
        );
    }

    public function verify(
        TeacherProfile $teacher
    ): RedirectResponse {
        $teacher->load([
            'user',
            'subjects',
            'locations',
        ]);

        if ($teacher->is_verified) {
            return back()->with(
                'error',
                'This teacher is already verified.'
            );
        }

        if (
            ! $teacher
                ->isCompleteForVerification()
        ) {
            $missing = implode(
                ', ',
                $teacher
                    ->missingVerificationFields()
            );

            return back()->with(
                'error',
                'Teacher profile is incomplete. Missing: '
                .$missing
                .'.'
            );
        }

        if (
            ! $teacher
                ->hasApprovedVerificationDocument()
        ) {
            return back()->with(
                'error',
                'This teacher cannot be verified until at least one verification document has been approved.'
            );
        }

        if (
            ! $teacher->user ||
            $teacher->user->status !== 'active'
        ) {
            return back()->with(
                'error',
                'This teacher account is not active.'
            );
        }

        $teacher->update([
            'is_verified' =>
                true,
        ]);

        UserNotificationService::send(
            $teacher->user,
            'Teacher Profile Verified',
            'Your teacher profile has been verified. You can now access tuition opportunities and appear in the teacher marketplace.',
            route(
                'teacher.dashboard'
            ),
            'teacher_verification'
        );

        return back()->with(
            'success',
            'Teacher verified successfully.'
        );
    }

    public function unverify(
        TeacherProfile $teacher
    ): RedirectResponse {
        $teacher->load(
            'user'
        );

        if (! $teacher->is_verified) {
            return back()->with(
                'error',
                'This teacher is already unverified.'
            );
        }

        $teacher->update([
            'is_verified' =>
                false,
        ]);

        if ($teacher->user) {
            UserNotificationService::send(
                $teacher->user,
                'Teacher Verification Removed',
                'Your teacher verification status has been removed. Please review your profile or contact support.',
                route(
                    'teacher.profile.edit'
                ),
                'teacher_verification'
            );
        }

        return back()->with(
            'success',
            'Teacher verification removed.'
        );
    }
}