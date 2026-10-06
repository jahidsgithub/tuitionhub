<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TuitionPost;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TuitionPostController extends Controller
{
    public function index(): View
    {
        $tuitions = TuitionPost::query()
            ->with([
                'location',
                'subjects',
            ])
            ->withCount('applications')
            ->where(
                'user_id',
                auth()->id()
            )
            ->latest()
            ->paginate(10);

        return view(
            'student.tuitions.index',
            compact('tuitions')
        );
    }

    public function create(): View|RedirectResponse
    {
        if (! $this->studentProfileComplete()) {
            return redirect()
                ->route('student.profile.edit')
                ->with(
                    'error',
                    'Please complete your student / guardian profile before posting a tuition.'
                );
        }

        $subjects = Subject::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $locations = Location::query()
            ->where('status', true)
            ->orderBy('district')
            ->orderBy('area')
            ->get();

        return view(
            'student.tuitions.create',
            compact(
                'subjects',
                'locations'
            )
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Server-side Profile Enforcement
        |--------------------------------------------------------------------------
        */

        if (! $this->studentProfileComplete()) {
            return redirect()
                ->route('student.profile.edit')
                ->with(
                    'error',
                    'Please complete your profile before posting a tuition.'
                );
        }

        $validated = $this->validateTuition(
            $request
        );

        $tuition = DB::transaction(
            function () use ($validated) {

                $tuition = TuitionPost::create([
                    'tuition_code' =>
                        $this->generateUniqueTuitionCode(),

                    'user_id' =>
                        auth()->id(),

                    'location_id' =>
                        $validated['location_id'] ?? null,

                    'title' =>
                        $validated['title'],

                    'class_level' =>
                        $validated['class_level'],

                    'medium' =>
                        $validated['medium'] ?? null,

                    'student_gender' =>
                        $validated['student_gender'] ?? null,

                    'preferred_teacher_gender' =>
                        $validated['preferred_teacher_gender'],

                    'days_per_week' =>
                        $validated['days_per_week'] ?? null,

                    'salary' =>
                        $validated['salary'] ?? null,

                    'teaching_mode' =>
                        $validated['teaching_mode'],

                    'requirements' =>
                        $validated['requirements'] ?? null,

                    'status' => 'pending',

                    'published_at' => null,
                ]);

                $tuition
                    ->subjects()
                    ->sync(
                        $validated['subjects']
                    );

                return $tuition;
            }
        );

        AdminNotificationService::send(
            'New Tuition Post Awaiting Review',
            auth()->user()->name
                .' submitted tuition '
                .$tuition->tuition_code
                .' for admin review.',
            route(
                'admin.tuitions.index',
                [
                    'status' => 'pending',
                    'search' => $tuition->tuition_code,
                ]
            ),
            'tuition_moderation'
        );

        return redirect()
            ->route(
                'student.tuitions.index'
            )
            ->with(
                'success',
                'Tuition post submitted successfully. It is now waiting for admin approval.'
            );
    }

    public function edit(
        TuitionPost $tuition
    ): View|RedirectResponse {
        $this->authorizeOwnership(
            $tuition
        );

        if (
            in_array(
                $tuition->status,
                [
                    'filled',
                    'closed',
                ],
                true
            )
        ) {
            return redirect()
                ->route(
                    'student.tuitions.index'
                )
                ->with(
                    'error',
                    'Filled or closed tuition posts cannot be edited.'
                );
        }

        $tuition->load('subjects');

        $subjects = Subject::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $locations = Location::query()
            ->where('status', true)
            ->orderBy('district')
            ->orderBy('area')
            ->get();

        return view(
            'student.tuitions.edit',
            compact(
                'tuition',
                'subjects',
                'locations'
            )
        );
    }

    public function update(
        Request $request,
        TuitionPost $tuition
    ): RedirectResponse {
        $this->authorizeOwnership(
            $tuition
        );

        if (
            in_array(
                $tuition->status,
                [
                    'filled',
                    'closed',
                ],
                true
            )
        ) {
            return back()->with(
                'error',
                'Filled or closed tuition posts cannot be edited.'
            );
        }

        $validated = $this->validateTuition(
            $request
        );

        DB::transaction(
            function () use (
                $tuition,
                $validated
            ) {
                $tuition->update([
                    'location_id' =>
                        $validated['location_id'] ?? null,

                    'title' =>
                        $validated['title'],

                    'class_level' =>
                        $validated['class_level'],

                    'medium' =>
                        $validated['medium'] ?? null,

                    'student_gender' =>
                        $validated['student_gender'] ?? null,

                    'preferred_teacher_gender' =>
                        $validated['preferred_teacher_gender'],

                    'days_per_week' =>
                        $validated['days_per_week'] ?? null,

                    'salary' =>
                        $validated['salary'] ?? null,

                    'teaching_mode' =>
                        $validated['teaching_mode'],

                    'requirements' =>
                        $validated['requirements'] ?? null,

                    'status' => 'pending',

                    'published_at' => null,
                ]);

                $tuition
                    ->subjects()
                    ->sync(
                        $validated['subjects']
                    );
            }
        );

        AdminNotificationService::send(
            'Tuition Post Updated',
            auth()->user()->name
                .' updated tuition '
                .$tuition->tuition_code
                .'. It requires admin review again.',
            route(
                'admin.tuitions.index',
                [
                    'status' => 'pending',
                    'search' => $tuition->tuition_code,
                ]
            ),
            'tuition_moderation'
        );

        return redirect()
            ->route(
                'student.tuitions.index'
            )
            ->with(
                'success',
                'Tuition updated successfully and sent for admin review.'
            );
    }

    public function close(
        TuitionPost $tuition
    ): RedirectResponse {
        $this->authorizeOwnership(
            $tuition
        );

        if ($tuition->status === 'filled') {
            return back()->with(
                'error',
                'A filled tuition cannot be closed manually.'
            );
        }

        if ($tuition->status === 'closed') {
            return back()->with(
                'error',
                'This tuition is already closed.'
            );
        }

        $tuition->update([
            'status' => 'closed',
        ]);

        return back()->with(
            'success',
            'Tuition post closed successfully.'
        );
    }

    public function destroy(
        TuitionPost $tuition
    ): RedirectResponse {
        $this->authorizeOwnership(
            $tuition
        );

        if (
            $tuition
                ->applications()
                ->exists()
        ) {
            return back()->with(
                'error',
                'This tuition cannot be deleted because applications already exist.'
            );
        }

        DB::transaction(
            function () use ($tuition) {
                $tuition
                    ->subjects()
                    ->detach();

                $tuition->delete();
            }
        );

        return redirect()
            ->route(
                'student.tuitions.index'
            )
            ->with(
                'success',
                'Tuition post deleted successfully.'
            );
    }

    private function validateTuition(
        Request $request
    ): array {
        return $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'class_level' => [
                'required',
                'string',
                'max:100',
            ],

            'medium' => [
                'nullable',
                'in:bangla,english,english_version,madrasa,other',
            ],

            'student_gender' => [
                'nullable',
                'in:male,female,other',
            ],

            'preferred_teacher_gender' => [
                'required',
                'in:male,female,any',
            ],

            'days_per_week' => [
                'nullable',
                'integer',
                'min:1',
                'max:7',
            ],

            'salary' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999',
            ],

            'teaching_mode' => [
                'required',
                'in:offline,online,both',
            ],

            'location_id' => [
                'nullable',
                'exists:locations,id',
            ],

            'requirements' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'subjects' => [
                'required',
                'array',
                'min:1',
            ],

            'subjects.*' => [
                'integer',
                'exists:subjects,id',
            ],
        ]);
    }

    private function generateUniqueTuitionCode(): string
    {
        do {
            $code = 'TH-'.Str::upper(
                Str::random(8)
            );
        } while (
            TuitionPost::where(
                'tuition_code',
                $code
            )->exists()
        );

        return $code;
    }

    private function authorizeOwnership(
        TuitionPost $tuition
    ): void {
        abort_unless(
            $tuition->user_id ===
            auth()->id(),
            403
        );
    }

    /**
     * Check student minimum profile.
     */
    private function studentProfileComplete(): bool
    {
        $profile = auth()
            ->user()
            ->studentProfile;

        return $profile &&
            $profile->isCompleteForMarketplace();
    }
}