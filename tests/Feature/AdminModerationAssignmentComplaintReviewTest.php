<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\TeacherReview;
use App\Models\TuitionAssignment;
use App\Models\TuitionPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminModerationAssignmentComplaintReviewTest extends TestCase
{
    use RefreshDatabase;

    private int $userCounter = 0;

    private function makeUser(
        string $role,
        array $overrides = []
    ): User {
        $this->userCounter++;

        return User::factory()->create(
            array_merge([
                'name' => ucfirst($role).' '.$this->userCounter,
                'email' => $role.$this->userCounter.'@example.com',
                'phone' => '01720'.str_pad(
                    (string) $this->userCounter,
                    6,
                    '0',
                    STR_PAD_LEFT
                ),
                'password' => Hash::make('Password@123'),
                'role' => $role,
                'status' => 'active',
                'email_verified_at' => now(),
            ], $overrides)
        );
    }

    private function makeAdmin(): User
    {
        return $this->makeUser('admin');
    }

    private function makeStudent(): User
    {
        $student = $this->makeUser('student');

        StudentProfile::query()->create([
            'user_id' => $student->id,
            'profile_photo' => null,
            'gender' => 'male',
            'guardian_name' => 'Test Guardian',
            'guardian_phone' => '01900000000',
            'class_level' => 'Class 10',
            'medium' => 'bangla',
            'address' => 'Dhaka',
        ]);

        return $student;
    }

    private function makeTeacher(): array
    {
        $teacher = $this->makeUser('teacher');

        $profile = TeacherProfile::query()->create([
            'user_id' => $teacher->id,
            'profile_photo' => null,
            'gender' => 'male',
            'university' => 'Test University',
            'department' => 'Mathematics',
            'degree' => 'Bachelor',
            'experience_years' => 3,
            'bio' => 'Test teacher profile.',
            'expected_salary_min' => 3000,
            'expected_salary_max' => 8000,
            'teaching_mode' => 'both',
            'is_verified' => true,
            'is_available' => true,
        ]);

        return [$teacher, $profile];
    }

    private function makeTuition(
        User $student,
        string $status = 'pending'
    ): TuitionPost {
        return TuitionPost::query()->create([
            'tuition_code' => 'TUI-'.strtoupper(
                substr(
                    md5(
                        $student->id.'-'.microtime(true)
                    ),
                    0,
                    8
                )
            ),

            'user_id' => $student->id,
            'location_id' => null,
            'title' => 'Class 10 Mathematics Tutor Needed',
            'class_level' => 'Class 10',
            'medium' => 'bangla',
            'student_gender' => 'male',
            'preferred_teacher_gender' => 'any',
            'days_per_week' => 3,
            'salary' => 5000,
            'teaching_mode' => 'offline',
            'requirements' => 'Experienced teacher preferred.',
            'status' => $status,
            'published_at' =>
                $status === 'published'
                    ? now()
                    : null,
        ]);
    }

    private function makeAssignment(
        User $student,
        TeacherProfile $teacherProfile,
        TuitionPost $tuition,
        string $status = 'active'
    ): TuitionAssignment {
        return TuitionAssignment::query()->create([
            'tuition_post_id' => $tuition->id,
            'teacher_profile_id' => $teacherProfile->id,
            'student_user_id' => $student->id,
            'source' => 'direct_request',
            'tuition_application_id' => null,
            'teacher_request_id' => null,
            'status' => $status,
            'assigned_at' => now(),
            'completed_at' =>
                $status === 'completed'
                    ? now()
                    : null,
            'cancelled_at' =>
                $status === 'cancelled'
                    ? now()
                    : null,
            'notes' => null,
        ]);
    }

    public function test_admin_can_view_tuition_moderation_list(): void
    {
        $admin = $this->makeAdmin();

        $response = $this
            ->actingAs($admin)
            ->get(
                route('admin.tuitions.index')
            );

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_admin_tuition_moderation(): void
    {
        $student = $this->makeStudent();

        $response = $this
            ->actingAs($student)
            ->get(
                route('admin.tuitions.index')
            );

        $response->assertForbidden();
    }

    public function test_admin_can_publish_pending_tuition(): void
    {
        $admin = $this->makeAdmin();

        $student = $this->makeStudent();

        $tuition = $this->makeTuition(
            $student,
            'pending'
        );

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.tuitions.publish',
                    $tuition
                )
            );

        $response->assertSessionHasNoErrors();

        $tuition->refresh();

        $this->assertSame(
            'published',
            $tuition->status
        );

        $this->assertNotNull(
            $tuition->published_at
        );
    }

    public function test_admin_can_move_published_tuition_back_to_pending(): void
    {
        $admin = $this->makeAdmin();

        $student = $this->makeStudent();

        $tuition = $this->makeTuition(
            $student,
            'published'
        );

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.tuitions.pending',
                    $tuition
                )
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'tuition_posts',
            [
                'id' => $tuition->id,
                'status' => 'pending',
            ]
        );
    }

    public function test_admin_can_close_published_tuition(): void
    {
        $admin = $this->makeAdmin();

        $student = $this->makeStudent();

        $tuition = $this->makeTuition(
            $student,
            'published'
        );

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.tuitions.close',
                    $tuition
                )
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'tuition_posts',
            [
                'id' => $tuition->id,
                'status' => 'closed',
            ]
        );
    }

    public function test_student_can_complete_own_active_assignment(): void
    {
        $student = $this->makeStudent();

        [, $teacherProfile] =
            $this->makeTeacher();

        $tuition = $this->makeTuition(
            $student,
            'filled'
        );

        $assignment = $this->makeAssignment(
            $student,
            $teacherProfile,
            $tuition
        );

        $response = $this
            ->actingAs($student)
            ->patch(
                route(
                    'student.assignments.complete',
                    $assignment
                )
            );

        $response->assertSessionHasNoErrors();

        $assignment->refresh();

        $this->assertSame(
            'completed',
            $assignment->status
        );

        $this->assertNotNull(
            $assignment->completed_at
        );
    }

    public function test_student_can_cancel_own_active_assignment(): void
    {
        $student = $this->makeStudent();

        [, $teacherProfile] =
            $this->makeTeacher();

        $tuition = $this->makeTuition(
            $student,
            'filled'
        );

        $assignment = $this->makeAssignment(
            $student,
            $teacherProfile,
            $tuition
        );

        $response = $this
            ->actingAs($student)
            ->patch(
                route(
                    'student.assignments.cancel',
                    $assignment
                )
            );

        $response->assertSessionHasNoErrors();

        $assignment->refresh();

        $this->assertSame(
            'cancelled',
            $assignment->status
        );

        $this->assertNotNull(
            $assignment->cancelled_at
        );
    }

    public function test_other_student_cannot_complete_someone_elses_assignment(): void
    {
        $owner = $this->makeStudent();

        $otherStudent = $this->makeStudent();

        [, $teacherProfile] =
            $this->makeTeacher();

        $tuition = $this->makeTuition(
            $owner,
            'filled'
        );

        $assignment = $this->makeAssignment(
            $owner,
            $teacherProfile,
            $tuition
        );

        $response = $this
            ->actingAs($otherStudent)
            ->patch(
                route(
                    'student.assignments.complete',
                    $assignment
                )
            );

        $response->assertForbidden();

        $this->assertDatabaseHas(
            'tuition_assignments',
            [
                'id' => $assignment->id,
                'status' => 'active',
            ]
        );
    }

    public function test_student_can_review_teacher_after_completed_assignment(): void
    {
        $student = $this->makeStudent();

        [, $teacherProfile] =
            $this->makeTeacher();

        $tuition = $this->makeTuition(
            $student,
            'filled'
        );

        $assignment = $this->makeAssignment(
            $student,
            $teacherProfile,
            $tuition,
            'completed'
        );

        $response = $this
            ->actingAs($student)
            ->post(
                route(
                    'student.assignments.review.store',
                    $assignment
                ),
                [
                    'rating' => 5,
                    'review' =>
                        'Excellent teacher and very helpful.',
                ]
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'teacher_reviews',
            [
                'tuition_assignment_id' =>
                    $assignment->id,

                'teacher_profile_id' =>
                    $teacherProfile->id,

                'student_user_id' =>
                    $student->id,

                'rating' => 5,
            ]
        );
    }

    public function test_student_cannot_review_active_assignment(): void
    {
        $student = $this->makeStudent();

        [, $teacherProfile] =
            $this->makeTeacher();

        $tuition = $this->makeTuition(
            $student,
            'filled'
        );

        $assignment = $this->makeAssignment(
            $student,
            $teacherProfile,
            $tuition,
            'active'
        );

        $response = $this
            ->actingAs($student)
            ->post(
                route(
                    'student.assignments.review.store',
                    $assignment
                ),
                [
                    'rating' => 5,
                    'review' =>
                        'Should not be accepted yet.',
                ]
            );

        $response->assertSessionHas(
            'error'
        );

        $this->assertDatabaseMissing(
            'teacher_reviews',
            [
                'tuition_assignment_id' =>
                    $assignment->id,
            ]
        );
    }

    public function test_completed_assignment_can_only_have_one_review(): void
    {
        $student = $this->makeStudent();

        [, $teacherProfile] =
            $this->makeTeacher();

        $tuition = $this->makeTuition(
            $student,
            'filled'
        );

        $assignment = $this->makeAssignment(
            $student,
            $teacherProfile,
            $tuition,
            'completed'
        );

        TeacherReview::query()->create([
            'tuition_assignment_id' =>
                $assignment->id,

            'teacher_profile_id' =>
                $teacherProfile->id,

            'student_user_id' =>
                $student->id,

            'rating' => 4,

            'review' =>
                'Existing review.',
        ]);

        $response = $this
            ->actingAs($student)
            ->post(
                route(
                    'student.assignments.review.store',
                    $assignment
                ),
                [
                    'rating' => 5,
                    'review' =>
                        'Second review attempt.',
                ]
            );

        $response->assertSessionHas(
            'error'
        );

        $this->assertSame(
            1,
            TeacherReview::query()
                ->where(
                    'tuition_assignment_id',
                    $assignment->id
                )
                ->count()
        );
    }

    public function test_student_can_submit_complaint_for_own_assignment(): void
    {
        $student = $this->makeStudent();

        [$teacher, $teacherProfile] =
            $this->makeTeacher();

        $tuition = $this->makeTuition(
            $student,
            'filled'
        );

        $assignment = $this->makeAssignment(
            $student,
            $teacherProfile,
            $tuition
        );

        $response = $this
            ->actingAs($student)
            ->post(
                route(
                    'student.complaints.store',
                    $assignment
                ),
                [
                    'category' => 'other',
                    'subject' => 'Test Complaint',
                    'description' =>
                        'This is a test complaint description.',
                ]
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'complaints',
            [
                'tuition_assignment_id' =>
                    $assignment->id,

                'reported_by' =>
                    $student->id,

                'reported_user_id' =>
                    $teacher->id,

                'status' =>
                    'open',
            ]
        );
    }

    public function test_teacher_can_submit_complaint_for_assignment(): void
    {
        $student = $this->makeStudent();

        [$teacher, $teacherProfile] =
            $this->makeTeacher();

        $tuition = $this->makeTuition(
            $student,
            'filled'
        );

        $assignment = $this->makeAssignment(
            $student,
            $teacherProfile,
            $tuition
        );

        $response = $this
            ->actingAs($teacher)
            ->post(
                route(
                    'teacher.complaints.store',
                    $assignment
                ),
                [
                    'category' => 'other',
                    'subject' => 'Student Complaint',
                    'description' =>
                        'Test complaint against student.',
                ]
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'complaints',
            [
                'tuition_assignment_id' =>
                    $assignment->id,

                'reported_by' =>
                    $teacher->id,

                'reported_user_id' =>
                    $student->id,

                'status' =>
                    'open',
            ]
        );
    }

    public function test_duplicate_unresolved_complaint_is_blocked(): void
    {
        $student = $this->makeStudent();

        [$teacher, $teacherProfile] =
            $this->makeTeacher();

        $tuition = $this->makeTuition(
            $student,
            'filled'
        );

        $assignment = $this->makeAssignment(
            $student,
            $teacherProfile,
            $tuition
        );

        Complaint::query()->create([
            'tuition_assignment_id' =>
                $assignment->id,

            'reported_by' =>
                $student->id,

            'reported_user_id' =>
                $teacher->id,

            'category' =>
                'other',

            'subject' =>
                'Existing Complaint',

            'description' =>
                'Existing unresolved complaint.',

            'status' =>
                'open',

            'unresolved_guard' =>
                1,
        ]);

        $response = $this
            ->actingAs($student)
            ->post(
                route(
                    'student.complaints.store',
                    $assignment
                ),
                [
                    'category' => 'other',
                    'subject' => 'Duplicate Complaint',
                    'description' =>
                        'Second unresolved complaint.',
                ]
            );

        $response->assertSessionHas(
            'error'
        );

        $this->assertSame(
            1,
            Complaint::query()
                ->where(
                    'tuition_assignment_id',
                    $assignment->id
                )
                ->where(
                    'reported_by',
                    $student->id
                )
                ->whereIn(
                    'status',
                    [
                        'open',
                        'investigating',
                    ]
                )
                ->count()
        );
    }

    public function test_admin_can_mark_complaint_investigating(): void
    {
        $admin = $this->makeAdmin();

        $student = $this->makeStudent();

        [$teacher, $teacherProfile] =
            $this->makeTeacher();

        $tuition = $this->makeTuition(
            $student,
            'filled'
        );

        $assignment = $this->makeAssignment(
            $student,
            $teacherProfile,
            $tuition
        );

        $complaint = Complaint::query()->create([
            'tuition_assignment_id' =>
                $assignment->id,

            'reported_by' =>
                $student->id,

            'reported_user_id' =>
                $teacher->id,

            'category' =>
                'other',

            'subject' =>
                'Test Complaint',

            'description' =>
                'Test description.',

            'status' =>
                'open',

            'unresolved_guard' =>
                1,
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.complaints.update',
                    $complaint
                ),
                [
                    'status' =>
                        'investigating',

                    'admin_note' =>
                        'Investigation started.',
                ]
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'complaints',
            [
                'id' =>
                    $complaint->id,

                'status' =>
                    'investigating',

                'handled_by' =>
                    $admin->id,
            ]
        );
    }

    public function test_admin_can_resolve_complaint_and_clear_unresolved_guard(): void
    {
        $admin = $this->makeAdmin();

        $student = $this->makeStudent();

        [$teacher, $teacherProfile] =
            $this->makeTeacher();

        $tuition = $this->makeTuition(
            $student,
            'filled'
        );

        $assignment = $this->makeAssignment(
            $student,
            $teacherProfile,
            $tuition
        );

        $complaint = Complaint::query()->create([
            'tuition_assignment_id' =>
                $assignment->id,

            'reported_by' =>
                $student->id,

            'reported_user_id' =>
                $teacher->id,

            'category' =>
                'other',

            'subject' =>
                'Resolvable Complaint',

            'description' =>
                'Test description.',

            'status' =>
                'open',

            'unresolved_guard' =>
                1,
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.complaints.update',
                    $complaint
                ),
                [
                    'status' =>
                        'resolved',

                    'admin_note' =>
                        'Complaint resolved.',
                ]
            );

        $response->assertSessionHasNoErrors();

        $complaint->refresh();

        $this->assertSame(
            'resolved',
            $complaint->status
        );

        $this->assertNull(
            $complaint->unresolved_guard
        );

        $this->assertSame(
            $admin->id,
            $complaint->handled_by
        );

        $this->assertNotNull(
            $complaint->resolved_at
        );
    }
}