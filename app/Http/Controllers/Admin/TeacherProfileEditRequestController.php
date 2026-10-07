<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeacherProfileEditRequest;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TeacherProfileEditRequestController extends Controller
{
    public function index(
        Request $request
    ): View {
        $query =
            TeacherProfileEditRequest::query()
                ->with([
                    'teacherProfile.user',
                    'teacherProfile.subjects',
                    'teacherProfile.locations',
                    'requestedBy',
                    'reviewedBy',
                ]);

        if ($request->filled('search')) {
            $search = trim(
                (string) $request->search
            );

            $query->whereHas(
                'teacherProfile.user',
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
            $query->where(
                'status',
                $request->status
            );
        }

        $editRequests =
            $query
                ->latest()
                ->paginate(15)
                ->withQueryString();

        $subjectIds =
            collect(
                $editRequests->items()
            )
                ->flatMap(
                    fn ($editRequest) =>
                        $editRequest
                            ->requested_data[
                                'subject_ids'
                            ] ?? []
                )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->unique()
                ->values();

        $locationIds =
            collect(
                $editRequests->items()
            )
                ->flatMap(
                    fn ($editRequest) =>
                        $editRequest
                            ->requested_data[
                                'location_ids'
                            ] ?? []
                )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->unique()
                ->values();

        $subjectsById =
            Subject::query()
                ->whereIn(
                    'id',
                    $subjectIds
                )
                ->get()
                ->keyBy('id');

        $locationsById =
            Location::query()
                ->whereIn(
                    'id',
                    $locationIds
                )
                ->get()
                ->keyBy('id');

        return view(
            'admin.teacher-profile-edit-requests.index',
            compact(
                'editRequests',
                'subjectsById',
                'locationsById'
            )
        );
    }

    public function approve(
        TeacherProfileEditRequest $editRequest
    ): RedirectResponse {
        $oldPhoto = null;
        $newPhoto = null;
        $teacherUser = null;

        $result = DB::transaction(
            function () use (
                $editRequest,
                &$oldPhoto,
                &$newPhoto,
                &$teacherUser
            ) {
                $lockedRequest =
                    TeacherProfileEditRequest::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $editRequest->id
                        );

                if (! $lockedRequest->isPending()) {
                    return false;
                }

                $teacher =
                    TeacherProfile::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $lockedRequest
                                ->teacher_profile_id
                        );

                $teacher->load('user');

                $data =
                    $lockedRequest
                        ->requested_data;

                $oldPhoto =
                    $teacher->profile_photo;

                $newPhoto =
                    $lockedRequest
                        ->profile_photo_path;

                $teacher->update([
                    'profile_photo' =>
                        $newPhoto
                            ?: $teacher
                                ->profile_photo,

                    'gender' =>
                        $data['gender']
                        ?? null,

                    'university' =>
                        $data['university']
                        ?? null,

                    'department' =>
                        $data['department']
                        ?? null,

                    'degree' =>
                        $data['degree']
                        ?? null,

                    'experience_years' =>
                        (int) (
                            $data[
                                'experience_years'
                            ] ?? 0
                        ),

                    'bio' =>
                        $data['bio']
                        ?? null,

                    'expected_salary_min' =>
                        $data[
                            'expected_salary_min'
                        ] ?? null,

                    'expected_salary_max' =>
                        $data[
                            'expected_salary_max'
                        ] ?? null,

                    'teaching_mode' =>
                        $data[
                            'teaching_mode'
                        ] ?? 'offline',

                    'is_available' =>
                        (bool) (
                            $data[
                                'is_available'
                            ] ?? false
                        ),
                ]);

                $teacher
                    ->subjects()
                    ->sync(
                        $data[
                            'subject_ids'
                        ] ?? []
                    );

                $teacher
                    ->locations()
                    ->sync(
                        $data[
                            'location_ids'
                        ] ?? []
                    );

                $lockedRequest->update([
                    'status' =>
                        TeacherProfileEditRequest::STATUS_APPROVED,

                    'active_key' =>
                        null,

                    'admin_note' =>
                        null,

                    'reviewed_by_user_id' =>
                        auth()->id(),

                    'reviewed_at' =>
                        now(),
                ]);

                $teacherUser =
                    $teacher->user;

                return true;
            }
        );

        if (! $result) {
            return back()->with(
                'error',
                'This edit request has already been reviewed.'
            );
        }

        if (
            $newPhoto &&
            $oldPhoto &&
            $newPhoto !== $oldPhoto
        ) {
            Storage::disk('public')
                ->delete(
                    $oldPhoto
                );
        }

        if ($teacherUser) {
            UserNotificationService::send(
                $teacherUser,
                'Profile Edit Request Approved',
                'Your requested teacher profile changes were approved and applied.',
                route(
                    'teacher.profile.edit'
                ),
                'teacher_profile_edit_request'
            );
        }

        return back()->with(
            'success',
            'Teacher profile edit request approved successfully.'
        );
    }

    public function reject(
        Request $request,
        TeacherProfileEditRequest $editRequest
    ): RedirectResponse {
        $validated =
            $request->validate([
                'admin_note' => [
                    'nullable',
                    'string',
                    'max:3000',
                ],
            ]);

        $pendingPhoto = null;
        $teacherUser = null;

        $result = DB::transaction(
            function () use (
                $editRequest,
                $validated,
                &$pendingPhoto,
                &$teacherUser
            ) {
                $lockedRequest =
                    TeacherProfileEditRequest::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $editRequest->id
                        );

                if (! $lockedRequest->isPending()) {
                    return false;
                }

                $lockedRequest->load(
                    'teacherProfile.user'
                );

                $pendingPhoto =
                    $lockedRequest
                        ->profile_photo_path;

                $teacherUser =
                    $lockedRequest
                        ->teacherProfile
                        ?->user;

                $lockedRequest->update([
                    'status' =>
                        TeacherProfileEditRequest::STATUS_REJECTED,

                    'active_key' =>
                        null,

                    'profile_photo_path' =>
                        null,

                    'admin_note' =>
                        $validated[
                            'admin_note'
                        ] ?? null,

                    'reviewed_by_user_id' =>
                        auth()->id(),

                    'reviewed_at' =>
                        now(),
                ]);

                return true;
            }
        );

        if (! $result) {
            return back()->with(
                'error',
                'This edit request has already been reviewed.'
            );
        }

        if ($pendingPhoto) {
            Storage::disk('public')
                ->delete(
                    $pendingPhoto
                );
        }

        if ($teacherUser) {
            $message =
                'Your teacher profile edit request was rejected.';

            if (
                ! empty(
                    $validated['admin_note']
                )
            ) {
                $message .=
                    ' Admin note: '
                    .$validated['admin_note'];
            }

            UserNotificationService::send(
                $teacherUser,
                'Profile Edit Request Rejected',
                $message,
                route(
                    'teacher.profile.edit'
                ),
                'teacher_profile_edit_request'
            );
        }

        return back()->with(
            'success',
            'Teacher profile edit request rejected.'
        );
    }
}