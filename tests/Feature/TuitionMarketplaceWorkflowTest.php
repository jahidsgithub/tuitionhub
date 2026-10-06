<?php

namespace Tests\Feature;

use App\Models\StudentProfile;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\TeacherProfile;
use App\Models\TeacherRequest;
use App\Models\TuitionApplication;
use App\Models\TuitionPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TuitionMarketplaceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private int $teacherCounter = 0;
    private int $studentCounter = 0;
    private int $planCounter = 0;

    private function makeTeacherUser(array $overrides = []): User
    {
        $this->teacherCounter++;

        return User::factory()->create(
            array_merge([
                'name' => 'Teacher '.$this->teacherCounter,
                'email' => 'teacher'.$this->teacherCounter.'@example.com',
                'phone' => '01710'.str_pad(
                    (string) $this->teacherCounter,
                    6,
                    '0',
                    STR_PAD_LEFT
                ),
                'password' => Hash::make('Password@123'),
                'role' => 'teacher',
                'status' => 'active',
                'email_verified_at' => now(),
            ], $overrides)
        );
    }

    private function makeStudentUser(array $overrides = []): User
    {
        $this->studentCounter++;

        return User::factory()->create(
            array_merge([
                'name' => 'Student '.$this->studentCounter,
                'email' => 'student'.$this->studentCounter.'@example.com',
                'phone' => '01810'.str_pad(
                    (string) $this->studentCounter,
                    6,
                    '0',
                    STR_PAD_LEFT
                ),
                'password' => Hash::make('Password@123'),
                'role' => 'student',
                'status' => 'active',
                'email_verified_at' => now(),
            ], $overrides)
        );
    }

    private function makeTeacherProfile(
        User $user,
        array $overrides = []
    ): TeacherProfile {
        return TeacherProfile::query()->create(
            array_merge([
                'user_id' => $user->id,
                'profile_photo' => null,
                'gender' => 'male',
                'university' => 'Test University',
                'department' => 'Test Department',
                'degree' => 'Bachelor',
                'experience_years' => 2,
                'bio' => 'Test teacher profile.',
                'expected_salary_min' => 3000,
                'expected_salary_max' => 8000,
                'teaching_mode' => 'both',
                'is_verified' => true,
                'is_available' => true,
            ], $overrides)
        );
    }

    private function makeStudentProfile(
        User $user,
        array $overrides = []
    ): StudentProfile {
        return StudentProfile::query()->create(
            array_merge([
                'user_id' => $user->id,
                'profile_photo' => null,
                'gender' => 'male',
                'guardian_name' => 'Test Guardian',
                'guardian_phone' => '01900000000',
                'class_level' => 'Class 10',
                'medium' => 'bangla',
                'address' => 'Dhaka',
            ], $overrides)
        );
    }

    private function makePublishedTuition(
        User $student,
        array $overrides = []
    ): TuitionPost {
        return TuitionPost::query()->create(
            array_merge([
                'tuition_code' =>
                    'TUI-'.strtoupper(
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
                'status' => 'published',
                'published_at' => now(),
            ], $overrides)
        );
    }

    private function makeSubscriptionPlan(
        int $limit = 10
    ): SubscriptionPlan {
        $this->planCounter++;

        return SubscriptionPlan::query()->create([
            'name' => 'Test Plan '.$this->planCounter,
            'slug' => 'test-plan-'.$this->planCounter,
            'description' => 'Test subscription plan.',
            'price' => 0,
            'duration_days' => 30,
            'application_limit' => $limit,
            'is_featured' => false,
            'status' => 'active',
            'sort_order' => $this->planCounter,
        ]);
    }

    private function makeActiveSubscription(
        User $teacher,
        int $limit = 10,
        int $used = 0
    ): Subscription {
        $plan = $this->makeSubscriptionPlan(
            $limit
        );

        return Subscription::query()->create([
            'user_id' => $teacher->id,
            'subscription_plan_id' => $plan->id,
            'plan_name_snapshot' => $plan->name,
            'amount' => $plan->price,
            'duration_days_snapshot' => $plan->duration_days,
            'application_limit_snapshot' => $limit,
            'plan_snapshot_captured_at' => now(),
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(
                $plan->duration_days
            ),
            'status' => 'active',
            'applications_used' => $used,
            'pending_user_id' => null,
        ]);
    }

    private function makeApplication(
        TuitionPost $tuition,
        TeacherProfile $teacher,
        array $overrides = []
    ): TuitionApplication {
        return TuitionApplication::query()->create(
            array_merge([
                'tuition_post_id' => $tuition->id,
                'teacher_profile_id' => $teacher->id,
                'message' => 'I am interested.',
                'expected_salary' => 5000,
                'status' => 'pending',
            ], $overrides)
        );
    }

    private function makeTeacherRequest(
        User $student,
        TeacherProfile $teacher,
        TuitionPost $tuition,
        array $overrides = []
    ): TeacherRequest {
        return TeacherRequest::query()->create(
            array_merge([
                'student_user_id' => $student->id,
                'teacher_profile_id' => $teacher->id,
                'tuition_post_id' => $tuition->id,
                'message' => 'Please teach me.',
                'status' => 'pending',
                'confirmed_at' => null,
            ], $overrides)
        );
    }

    public function test_verified_available_teacher_can_view_tuition_list(): void
    {
        $teacher = $this->makeTeacherUser();

        $this->makeTeacherProfile($teacher);

        $response = $this
            ->actingAs($teacher)
            ->get(
                route('teacher.tuitions.index')
            );

        $response->assertOk();
    }

    public function test_unverified_teacher_cannot_browse_tuitions(): void
    {
        $teacher = $this->makeTeacherUser();

        $this->makeTeacherProfile(
            $teacher,
            [
                'is_verified' => false,
            ]
        );

        $response = $this
            ->actingAs($teacher)
            ->get(
                route('teacher.tuitions.index')
            );

        $response->assertRedirect(
            route('teacher.profile.edit')
        );
    }

    public function test_unavailable_teacher_cannot_browse_tuitions(): void
    {
        $teacher = $this->makeTeacherUser();

        $this->makeTeacherProfile(
            $teacher,
            [
                'is_available' => false,
            ]
        );

        $response = $this
            ->actingAs($teacher)
            ->get(
                route('teacher.tuitions.index')
            );

        $response->assertRedirect(
            route('teacher.profile.edit')
        );
    }

    public function test_verified_teacher_with_active_subscription_can_apply_for_tuition(): void
    {
        $teacher =
            $this->makeTeacherUser();

        $teacherProfile =
            $this->makeTeacherProfile(
                $teacher
            );

        $student =
            $this->makeStudentUser();

        $this->makeStudentProfile(
            $student
        );

        $tuition =
            $this->makePublishedTuition(
                $student
            );

        $subscription =
            $this->makeActiveSubscription(
                $teacher
            );

        $response = $this
            ->actingAs($teacher)
            ->post(
                route(
                    'teacher.tuitions.apply',
                    $tuition
                ),
                [
                    'message' =>
                        'I am interested.',
                    'expected_salary' =>
                        5000,
                ]
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'tuition_applications',
            [
                'tuition_post_id' =>
                    $tuition->id,
                'teacher_profile_id' =>
                    $teacherProfile->id,
                'status' =>
                    'pending',
            ]
        );

        $this->assertSame(
            1,
            $subscription
                ->fresh()
                ->applications_used
        );
    }

    public function test_teacher_cannot_apply_twice_for_same_tuition(): void
    {
        $teacher =
            $this->makeTeacherUser();

        $teacherProfile =
            $this->makeTeacherProfile(
                $teacher
            );

        $student =
            $this->makeStudentUser();

        $this->makeStudentProfile(
            $student
        );

        $tuition =
            $this->makePublishedTuition(
                $student
            );

        $subscription =
            $this->makeActiveSubscription(
                $teacher
            );

        $this->makeApplication(
            $tuition,
            $teacherProfile
        );

        $response = $this
            ->actingAs($teacher)
            ->post(
                route(
                    'teacher.tuitions.apply',
                    $tuition
                ),
                [
                    'message' =>
                        'Second attempt.',
                    'expected_salary' =>
                        5000,
                ]
            );

        $response->assertSessionHas(
            'error'
        );

        $this->assertSame(
            1,
            TuitionApplication::query()
                ->where(
                    'tuition_post_id',
                    $tuition->id
                )
                ->where(
                    'teacher_profile_id',
                    $teacherProfile->id
                )
                ->count()
        );

        $this->assertSame(
            0,
            $subscription
                ->fresh()
                ->applications_used
        );
    }

    public function test_teacher_without_active_subscription_cannot_apply(): void
    {
        $teacher =
            $this->makeTeacherUser();

        $teacherProfile =
            $this->makeTeacherProfile(
                $teacher
            );

        $student =
            $this->makeStudentUser();

        $this->makeStudentProfile(
            $student
        );

        $tuition =
            $this->makePublishedTuition(
                $student
            );

        $response = $this
            ->actingAs($teacher)
            ->post(
                route(
                    'teacher.tuitions.apply',
                    $tuition
                ),
                [
                    'message' =>
                        'Interested.',
                    'expected_salary' =>
                        5000,
                ]
            );

        $response->assertRedirect(
            route(
                'teacher.subscription.index'
            )
        );

        $this->assertDatabaseMissing(
            'tuition_applications',
            [
                'tuition_post_id' =>
                    $tuition->id,
                'teacher_profile_id' =>
                    $teacherProfile->id,
            ]
        );
    }

    public function test_application_limit_is_enforced(): void
    {
        $teacher =
            $this->makeTeacherUser();

        $teacherProfile =
            $this->makeTeacherProfile(
                $teacher
            );

        $student =
            $this->makeStudentUser();

        $this->makeStudentProfile(
            $student
        );

        $tuition =
            $this->makePublishedTuition(
                $student
            );

        $subscription =
            $this->makeActiveSubscription(
                $teacher,
                1,
                1
            );

        $response = $this
            ->actingAs($teacher)
            ->post(
                route(
                    'teacher.tuitions.apply',
                    $tuition
                ),
                [
                    'message' =>
                        'Interested.',
                    'expected_salary' =>
                        5000,
                ]
            );

        $response->assertRedirect(
            route(
                'teacher.subscription.index'
            )
        );

        $this->assertDatabaseMissing(
            'tuition_applications',
            [
                'tuition_post_id' =>
                    $tuition->id,
                'teacher_profile_id' =>
                    $teacherProfile->id,
            ]
        );

        $this->assertSame(
            1,
            $subscription
                ->fresh()
                ->applications_used
        );
    }

    public function test_student_can_send_direct_teacher_request(): void
    {
        $student =
            $this->makeStudentUser();

        $this->makeStudentProfile(
            $student
        );

        $teacher =
            $this->makeTeacherUser();

        $teacherProfile =
            $this->makeTeacherProfile(
                $teacher
            );

        $tuition =
            $this->makePublishedTuition(
                $student
            );

        $response = $this
            ->actingAs($student)
            ->post(
                route(
                    'student.teachers.request',
                    $teacherProfile
                ),
                [
                    'tuition_post_id' =>
                        $tuition->id,
                    'message' =>
                        'Please teach me.',
                ]
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'teacher_requests',
            [
                'student_user_id' =>
                    $student->id,
                'teacher_profile_id' =>
                    $teacherProfile->id,
                'tuition_post_id' =>
                    $tuition->id,
                'status' =>
                    'pending',
            ]
        );
    }

    public function test_teacher_can_accept_direct_request(): void
    {
        $student =
            $this->makeStudentUser();

        $this->makeStudentProfile(
            $student
        );

        $teacher =
            $this->makeTeacherUser();

        $teacherProfile =
            $this->makeTeacherProfile(
                $teacher
            );

        $tuition =
            $this->makePublishedTuition(
                $student
            );

        $teacherRequest =
            $this->makeTeacherRequest(
                $student,
                $teacherProfile,
                $tuition
            );

        $response = $this
            ->actingAs($teacher)
            ->patch(
                route(
                    'teacher.requests.accept',
                    $teacherRequest
                )
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'teacher_requests',
            [
                'id' =>
                    $teacherRequest->id,
                'status' =>
                    'accepted',
            ]
        );
    }

    public function test_teacher_can_reject_direct_request(): void
    {
        $student =
            $this->makeStudentUser();

        $this->makeStudentProfile(
            $student
        );

        $teacher =
            $this->makeTeacherUser();

        $teacherProfile =
            $this->makeTeacherProfile(
                $teacher
            );

        $tuition =
            $this->makePublishedTuition(
                $student
            );

        $teacherRequest =
            $this->makeTeacherRequest(
                $student,
                $teacherProfile,
                $tuition
            );

        $response = $this
            ->actingAs($teacher)
            ->patch(
                route(
                    'teacher.requests.reject',
                    $teacherRequest
                )
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'teacher_requests',
            [
                'id' =>
                    $teacherRequest->id,
                'status' =>
                    'rejected',
            ]
        );
    }

    public function test_student_can_cancel_pending_direct_request(): void
    {
        $student =
            $this->makeStudentUser();

        $this->makeStudentProfile(
            $student
        );

        $teacher =
            $this->makeTeacherUser();

        $teacherProfile =
            $this->makeTeacherProfile(
                $teacher
            );

        $tuition =
            $this->makePublishedTuition(
                $student
            );

        $teacherRequest =
            $this->makeTeacherRequest(
                $student,
                $teacherProfile,
                $tuition
            );

        $response = $this
            ->actingAs($student)
            ->patch(
                route(
                    'student.teacher-requests.cancel',
                    $teacherRequest
                )
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'teacher_requests',
            [
                'id' =>
                    $teacherRequest->id,
                'status' =>
                    'cancelled',
            ]
        );
    }

    public function test_student_can_confirm_accepted_teacher_and_create_assignment(): void
    {
        $student =
            $this->makeStudentUser();

        $this->makeStudentProfile(
            $student
        );

        $teacher =
            $this->makeTeacherUser();

        $teacherProfile =
            $this->makeTeacherProfile(
                $teacher
            );

        $tuition =
            $this->makePublishedTuition(
                $student
            );

        $teacherRequest =
            $this->makeTeacherRequest(
                $student,
                $teacherProfile,
                $tuition,
                [
                    'status' =>
                        'accepted',
                    'confirmed_at' =>
                        null,
                ]
            );

        $response = $this
            ->actingAs($student)
            ->patch(
                route(
                    'student.teacher-requests.confirm',
                    $teacherRequest
                )
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'tuition_assignments',
            [
                'tuition_post_id' =>
                    $tuition->id,
                'teacher_profile_id' =>
                    $teacherProfile->id,
                'student_user_id' =>
                    $student->id,
                'source' =>
                    'direct_request',
                'teacher_request_id' =>
                    $teacherRequest->id,
                'status' =>
                    'active',
            ]
        );

        $this->assertDatabaseHas(
            'tuition_posts',
            [
                'id' =>
                    $tuition->id,
                'status' =>
                    'filled',
            ]
        );

        $this->assertNotNull(
            $teacherRequest
                ->fresh()
                ->confirmed_at
        );
    }

    public function test_confirming_teacher_rejects_competing_applications(): void
    {
        $student =
            $this->makeStudentUser();

        $this->makeStudentProfile(
            $student
        );

        $selectedTeacher =
            $this->makeTeacherUser();

        $selectedProfile =
            $this->makeTeacherProfile(
                $selectedTeacher
            );

        $competingTeacher =
            $this->makeTeacherUser();

        $competingProfile =
            $this->makeTeacherProfile(
                $competingTeacher
            );

        $tuition =
            $this->makePublishedTuition(
                $student
            );

        $teacherRequest =
            $this->makeTeacherRequest(
                $student,
                $selectedProfile,
                $tuition,
                [
                    'status' =>
                        'accepted',
                ]
            );

        $application =
            $this->makeApplication(
                $tuition,
                $competingProfile
            );

        $response = $this
            ->actingAs($student)
            ->patch(
                route(
                    'student.teacher-requests.confirm',
                    $teacherRequest
                )
            );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'tuition_applications',
            [
                'id' =>
                    $application->id,
                'status' =>
                    'rejected',
            ]
        );
    }
}