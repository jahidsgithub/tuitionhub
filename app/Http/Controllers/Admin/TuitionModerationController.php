<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TuitionPost;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TuitionModerationController extends Controller
{
    /**
     * Show all tuition posts for moderation.
     */
    public function index(
        Request $request
    ): View {
        $query = TuitionPost::query()
            ->with([
                'user',
                'location',
                'subjects',
            ])
            ->withCount('applications');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim(
                $request->search
            );

            $query->where(
                function ($q) use ($search) {
                    $q->where(
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
                        )
                        ->orWhereHas(
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
            );
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
        | Teaching Mode Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('teaching_mode')) {
            $query->where(
                'teaching_mode',
                $request->teaching_mode
            );
        }

        $tuitions = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'admin.tuitions.index',
            compact('tuitions')
        );
    }

    /**
     * Publish tuition.
     */
    public function publish(
        TuitionPost $tuition
    ): RedirectResponse {
        $result = DB::transaction(
            function () use ($tuition) {
                $lockedTuition = TuitionPost::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $tuition->id
                    );

                if (
                    $lockedTuition->status ===
                    'filled'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'A filled tuition cannot be published again.',
                    ];
                }

                if (
                    $lockedTuition->status ===
                    'closed'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'A closed tuition cannot be published.',
                    ];
                }

                if (
                    $lockedTuition->status ===
                    'published'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'This tuition is already published.',
                    ];
                }

                $lockedTuition->update([
                    'status' =>
                        'published',

                    'published_at' =>
                        now(),
                ]);

                return [
                    'success' =>
                        true,

                    'tuition_id' =>
                        $lockedTuition->id,
                ];
            }
        );

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        $tuition = TuitionPost::query()
            ->with('user')
            ->find(
                $result[
                    'tuition_id'
                ]
            );

        if ($tuition?->user) {
            UserNotificationService::send(
                $tuition->user,
                'Tuition Post Approved',
                'Your tuition '
                    .$tuition->tuition_code
                    .' has been approved and is now visible to teachers.',
                route(
                    'student.tuitions.index'
                ),
                'tuition_moderation'
            );
        }

        return back()->with(
            'success',
            'Tuition published successfully.'
        );
    }

    /**
     * Return tuition to pending review.
     */
    public function pending(
        TuitionPost $tuition
    ): RedirectResponse {
        $result = DB::transaction(
            function () use ($tuition) {
                $lockedTuition =
                    TuitionPost::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $tuition->id
                        );

                if (
                    $lockedTuition
                        ->status ===
                    'filled'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'A filled tuition cannot be moved back to pending.',
                    ];
                }

                if (
                    $lockedTuition
                        ->status ===
                    'closed'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'A closed tuition cannot be moved back to pending.',
                    ];
                }

                if (
                    $lockedTuition
                        ->status ===
                    'pending'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'This tuition is already pending.',
                    ];
                }

                $lockedTuition->update([
                    'status' =>
                        'pending',

                    'published_at' =>
                        null,
                ]);

                return [
                    'success' =>
                        true,

                    'tuition_id' =>
                        $lockedTuition->id,
                ];
            }
        );

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        $tuition = TuitionPost::query()
            ->with('user')
            ->find(
                $result[
                    'tuition_id'
                ]
            );

        if ($tuition?->user) {
            UserNotificationService::send(
                $tuition->user,
                'Tuition Post Under Review',
                'Your tuition '
                    .$tuition->tuition_code
                    .' has been moved back to pending review by the admin.',
                route(
                    'student.tuitions.index'
                ),
                'tuition_moderation'
            );
        }

        return back()->with(
            'success',
            'Tuition moved to pending review.'
        );
    }

    /**
     * Close tuition.
     */
    public function close(
        TuitionPost $tuition
    ): RedirectResponse {
        $result = DB::transaction(
            function () use ($tuition) {
                $lockedTuition =
                    TuitionPost::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $tuition->id
                        );

                if (
                    $lockedTuition
                        ->status ===
                    'filled'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'A filled tuition cannot be closed by moderation.',
                    ];
                }

                if (
                    $lockedTuition
                        ->status ===
                    'closed'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'This tuition is already closed.',
                    ];
                }

                $lockedTuition->update([
                    'status' =>
                        'closed',
                ]);

                return [
                    'success' =>
                        true,

                    'tuition_id' =>
                        $lockedTuition->id,
                ];
            }
        );

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        $tuition = TuitionPost::query()
            ->with('user')
            ->find(
                $result[
                    'tuition_id'
                ]
            );

        if ($tuition?->user) {
            UserNotificationService::send(
                $tuition->user,
                'Tuition Post Closed',
                'Your tuition '
                    .$tuition->tuition_code
                    .' has been closed by the admin.',
                route(
                    'student.tuitions.index'
                ),
                'tuition_moderation'
            );
        }

        return back()->with(
            'success',
            'Tuition closed successfully.'
        );
    }
}