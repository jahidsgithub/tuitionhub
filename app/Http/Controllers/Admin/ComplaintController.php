<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function index(
        Request $request
    ): View {
        $query = Complaint::query()
            ->with([
                'reporter',
                'reportedUser',
                'handler',
                'assignment.tuitionPost',
            ]);

        if ($request->filled('search')) {
            $search = trim(
                (string) $request->search
            );

            $query->where(
                function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->orWhere(
                            'id',
                            (int) $search
                        );
                    }

                    $q->orWhere(
                        'subject',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhereHas(
                            'reporter',
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
                                    );
                            }
                        )
                        ->orWhereHas(
                            'reportedUser',
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
                                    );
                            }
                        );
                }
            );
        }

        if (
            $request->filled('status') &&
            in_array(
                $request->status,
                [
                    'open',
                    'investigating',
                    'resolved',
                    'rejected',
                ],
                true
            )
        ) {
            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('category')) {
            $query->where(
                'category',
                $request->category
            );
        }

        $openCount = Complaint::query()
            ->where('status', 'open')
            ->count();

        $investigatingCount = Complaint::query()
            ->where(
                'status',
                'investigating'
            )
            ->count();

        $resolvedCount = Complaint::query()
            ->where(
                'status',
                'resolved'
            )
            ->count();

        $rejectedCount = Complaint::query()
            ->where(
                'status',
                'rejected'
            )
            ->count();

        $complaints = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.complaints.index',
            compact(
                'complaints',
                'openCount',
                'investigatingCount',
                'resolvedCount',
                'rejectedCount'
            )
        );
    }

    public function update(
        Request $request,
        Complaint $complaint
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:open,investigating,resolved,rejected',
            ],

            'admin_note' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $result = DB::transaction(
            function () use (
                $complaint,
                $validated
            ) {
                $lockedComplaint =
                    Complaint::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $complaint->id
                        );

                $lockedComplaint->update([
                    'status' =>
                        $validated['status'],

                    'admin_note' =>
                        $validated['admin_note']
                        ?? null,

                    'handled_by' =>
                        auth()->id(),

                    'resolved_at' =>
                        in_array(
                            $validated['status'],
                            [
                                'resolved',
                                'rejected',
                            ],
                            true
                        )
                            ? now()
                            : null,
                ]);

                return [
                    'complaint_id' =>
                        $lockedComplaint->id,
                ];
            }
        );

        $complaint = Complaint::query()
            ->with('reporter')
            ->find(
                $result['complaint_id']
            );

        if ($complaint?->reporter) {
            UserNotificationService::send(
                $complaint->reporter,

                'Complaint Status Updated',

                'Complaint #'
                .$complaint->id
                .' is now '
                .ucfirst(
                    $complaint->status
                )
                .'.',

                $complaint
                    ->reporter
                    ->isTeacher()
                    ? route(
                        'teacher.complaints.index'
                    )
                    : route(
                        'student.complaints.index'
                    ),

                'complaint'
            );
        }

        return back()->with(
            'success',
            'Complaint status updated successfully.'
        );
    }
}