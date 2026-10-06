<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherVerificationDocument;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationDocumentController extends Controller
{
    public function index(
        Request $request
    ): View {
        $query =
            TeacherVerificationDocument::query()
                ->with([
                    'teacherProfile.user',
                    'reviewer',
                ]);

        if ($request->filled('search')) {
            $search = trim(
                $request->search
            );

            $query->where(
                function ($q) use ($search) {
                    $q->where(
                        'document_name',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhereHas(
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
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('type')) {
            $query->where(
                'document_type',
                $request->type
            );
        }

        $documents = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.verification-documents.index',
            compact('documents')
        );
    }

    /**
     * Secure admin-only document preview.
     */
    public function view(
        TeacherVerificationDocument $document
    ): StreamedResponse {
        abort_unless(
            Storage::disk('local')
                ->exists($document->file_path),
            404
        );

        return Storage::disk('local')
            ->response(
                $document->file_path,
                basename(
                    $document->file_path
                ),
                [
                    'Content-Disposition' =>
                        'inline; filename="'
                        .basename($document->file_path)
                        .'"',
                ]
            );
    }

    public function approve(
        TeacherVerificationDocument $document
    ): RedirectResponse {
        $result = DB::transaction(
            function () use ($document) {
                $lockedDocument =
                    TeacherVerificationDocument::query()
                        ->with(
                            'teacherProfile.user'
                        )
                        ->lockForUpdate()
                        ->findOrFail(
                            $document->id
                        );

                if (
                    $lockedDocument->status ===
                    'approved'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'This document is already approved.',
                    ];
                }

                $lockedDocument->update([
                    'status' =>
                        'approved',

                    'admin_note' =>
                        null,

                    'reviewed_at' =>
                        now(),

                    'reviewed_by' =>
                        auth()->id(),
                ]);

                return [
                    'success' => true,
                    'document_id' =>
                        $lockedDocument->id,
                ];
            }
        );

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        $document =
            TeacherVerificationDocument::query()
                ->with(
                    'teacherProfile.user'
                )
                ->find(
                    $result['document_id']
                );

        if (
            $document
                ?->teacherProfile
                ?->user
        ) {
            UserNotificationService::send(
                $document
                    ->teacherProfile
                    ->user,
                'Verification Document Approved',
                'One of your teacher verification documents has been approved.',
                route(
                    'teacher.verification-documents.index'
                ),
                'teacher_verification'
            );
        }

        return back()->with(
            'success',
            'Verification document approved successfully.'
        );
    }

    public function reject(
        Request $request,
        TeacherVerificationDocument $document
    ): RedirectResponse {
        $validated =
            $request->validate([
                'admin_note' => [
                    'required',
                    'string',
                    'max:2000',
                ],
            ]);

        $result = DB::transaction(
            function () use (
                $document,
                $validated
            ) {
                $lockedDocument =
                    TeacherVerificationDocument::query()
                        ->with(
                            'teacherProfile.user'
                        )
                        ->lockForUpdate()
                        ->findOrFail(
                            $document->id
                        );

                /*
                |--------------------------------------------------------------------------
                | Approved Documents Cannot Be Rejected
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedDocument->status ===
                    'approved'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'An approved document cannot be rejected. Unverify the teacher separately if a verification issue is discovered.',
                    ];
                }

                if (
                    $lockedDocument->status ===
                    'rejected'
                ) {
                    return [
                        'success' => false,
                        'message' =>
                            'This document is already rejected.',
                    ];
                }

                $lockedDocument->update([
                    'status' =>
                        'rejected',

                    'admin_note' =>
                        $validated['admin_note'],

                    'reviewed_at' =>
                        now(),

                    'reviewed_by' =>
                        auth()->id(),
                ]);

                return [
                    'success' => true,
                    'document_id' =>
                        $lockedDocument->id,
                ];
            }
        );

        if (! $result['success']) {
            return back()->with(
                'error',
                $result['message']
            );
        }

        $document =
            TeacherVerificationDocument::query()
                ->with(
                    'teacherProfile.user'
                )
                ->find(
                    $result['document_id']
                );

        if (
            $document
                ?->teacherProfile
                ?->user
        ) {
            UserNotificationService::send(
                $document
                    ->teacherProfile
                    ->user,
                'Verification Document Rejected',
                'A teacher verification document was rejected. Please review the admin note and upload a corrected document if necessary.',
                route(
                    'teacher.verification-documents.index'
                ),
                'teacher_verification'
            );
        }

        return back()->with(
            'success',
            'Verification document rejected.'
        );
    }
}