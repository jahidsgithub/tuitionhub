<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherVerificationDocument;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationDocumentController extends Controller
{
    public function index(): View
    {
        $profile = auth()
            ->user()
            ->teacherProfile;

        if (! $profile) {
            abort(404);
        }

        $documents = $profile
            ->verificationDocuments()
            ->latest()
            ->get();

        return view(
            'teacher.verification-documents.index',
            compact(
                'profile',
                'documents'
            )
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $profile = auth()
            ->user()
            ->teacherProfile;

        if (! $profile) {
            return redirect()
                ->route('teacher.profile.edit')
                ->with(
                    'error',
                    'Please complete your teacher profile first.'
                );
        }

        $validated = $request->validate([
            'document_type' => [
                'required',
                'in:nid,student_id,certificate,other',
            ],

            'document_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Store Privately
        |--------------------------------------------------------------------------
        */

        $filePath = $request
            ->file('file')
            ->store(
                'teacher-verification-documents/'.$profile->id,
                'local'
            );

        try {
            $document = DB::transaction(
                function () use (
                    $profile,
                    $validated,
                    $filePath
                ) {
                    $document =
                        TeacherVerificationDocument::create([
                            'teacher_profile_id' =>
                                $profile->id,

                            'document_type' =>
                                $validated['document_type'],

                            'document_name' =>
                                $validated['document_name'] ?? null,

                            'file_path' =>
                                $filePath,

                            'status' =>
                                'pending',
                        ]);

                    /*
                    |--------------------------------------------------------------------------
                    | New Verification Material Requires Review
                    |--------------------------------------------------------------------------
                    */

                    if ($profile->is_verified) {
                        $profile->update([
                            'is_verified' => false,
                        ]);
                    }

                    return $document;
                }
            );
        } catch (\Throwable $e) {
            Storage::disk('local')
                ->delete($filePath);

            throw $e;
        }

        AdminNotificationService::send(
            'New Verification Document',
            auth()->user()->name
                .' uploaded a new teacher verification document.',
            route(
                'admin.verification-documents.index',
                [
                    'status' => 'pending',
                ]
            ),
            'teacher_verification'
        );

        return redirect()
            ->route(
                'teacher.verification-documents.index'
            )
            ->with(
                'success',
                'Verification document uploaded securely and sent for admin review.'
            );
    }

    /**
     * Securely display teacher's own document.
     */
    public function view(
        TeacherVerificationDocument $document
    ): StreamedResponse {
        $profile = auth()
            ->user()
            ->teacherProfile;

        abort_unless(
            $profile &&
            $document->teacher_profile_id ===
            $profile->id,
            403
        );

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

    public function destroy(
        TeacherVerificationDocument $document
    ): RedirectResponse {
        $profile = auth()
            ->user()
            ->teacherProfile;

        abort_unless(
            $profile &&
            $document->teacher_profile_id ===
            $profile->id,
            403
        );

        if ($document->status === 'approved') {
            return back()->with(
                'error',
                'Approved verification documents cannot be deleted.'
            );
        }

        $filePath =
            $document->file_path;

        DB::transaction(
            function () use ($document) {
                $document->delete();
            }
        );

        Storage::disk('local')
            ->delete($filePath);

        return back()->with(
            'success',
            'Verification document deleted successfully.'
        );
    }
}