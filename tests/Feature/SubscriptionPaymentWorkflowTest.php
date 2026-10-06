<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SubscriptionPaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private int $userCounter = 0;
    private int $planCounter = 0;
    private int $transactionCounter = 0;

    private function makeUser(
        string $role,
        array $overrides = []
    ): User {
        $this->userCounter++;

        return User::factory()->create(
            array_merge([
                'name' => ucfirst($role).' '.$this->userCounter,
                'email' => $role.$this->userCounter.'@example.com',
                'phone' => '01730'.str_pad(
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

    private function makeTeacher(): User
    {
        return $this->makeUser('teacher');
    }

    private function makeAdmin(): User
    {
        return $this->makeUser('admin');
    }

    private function makePlan(
        array $overrides = []
    ): SubscriptionPlan {
        $this->planCounter++;

        return SubscriptionPlan::query()->create(
            array_merge([
                'name' => 'Test Plan '.$this->planCounter,
                'slug' => 'test-plan-'.$this->planCounter,
                'description' => 'Test subscription plan.',
                'price' => 500,
                'duration_days' => 30,
                'application_limit' => 10,
                'is_featured' => false,
                'status' => 'active',
                'sort_order' => $this->planCounter,
            ], $overrides)
        );
    }

    private function makePendingSubscription(
        User $teacher,
        SubscriptionPlan $plan,
        array $overrides = []
    ): Subscription {
        return Subscription::query()->create(
            array_merge([
                'user_id' => $teacher->id,
                'subscription_plan_id' => $plan->id,

                'plan_name_snapshot' => $plan->name,

                'amount' => $plan->price,

                'duration_days_snapshot' =>
                    $plan->duration_days,

                'application_limit_snapshot' =>
                    $plan->application_limit,

                'plan_snapshot_captured_at' => now(),

                'starts_at' => null,
                'expires_at' => null,

                'status' => 'pending',

                'applications_used' => 0,

                'pending_user_id' => $teacher->id,
            ], $overrides)
        );
    }

    private function makeActiveSubscription(
        User $teacher,
        SubscriptionPlan $plan,
        array $overrides = []
    ): Subscription {
        return Subscription::query()->create(
            array_merge([
                'user_id' => $teacher->id,
                'subscription_plan_id' => $plan->id,

                'plan_name_snapshot' => $plan->name,

                'amount' => $plan->price,

                'duration_days_snapshot' =>
                    $plan->duration_days,

                'application_limit_snapshot' =>
                    $plan->application_limit,

                'plan_snapshot_captured_at' => now(),

                'starts_at' => now()->subDays(10),

                'expires_at' => now()->addDays(20),

                'status' => 'active',

                'applications_used' => 2,

                'pending_user_id' => null,
            ], $overrides)
        );
    }

    private function makePendingPayment(
        User $teacher,
        Subscription $subscription,
        array $overrides = []
    ): Payment {
        $this->transactionCounter++;

        return Payment::query()->create(
            array_merge([
                'user_id' => $teacher->id,

                'subscription_id' =>
                    $subscription->id,

                'transaction_id' =>
                    'TXN-TEST-'.$this->transactionCounter,

                'payment_method' => 'bkash',

                'amount' => $subscription->amount,

                'currency' => 'BDT',

                'status' => 'pending',

                'gateway_response' => null,

                'paid_at' => null,

                'pending_subscription_id' =>
                    $subscription->id,
            ], $overrides)
        );
    }

    public function test_admin_can_view_payment_list(): void
    {
        $admin = $this->makeAdmin();

        $response = $this
            ->actingAs($admin)
            ->get(
                route('admin.payments.index')
            );

        $response->assertOk();
    }

    public function test_teacher_cannot_access_admin_payment_list(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this
            ->actingAs($teacher)
            ->get(
                route('admin.payments.index')
            );

        $response->assertForbidden();
    }

    public function test_teacher_can_open_payment_page_for_own_pending_subscription(): void
    {
        $teacher = $this->makeTeacher();

        $plan = $this->makePlan();

        $subscription =
            $this->makePendingSubscription(
                $teacher,
                $plan
            );

        $response = $this
            ->actingAs($teacher)
            ->get(
                route(
                    'teacher.payment.create',
                    $subscription
                )
            );

        $response->assertOk();
    }

    public function test_teacher_cannot_open_another_teachers_payment_page(): void
    {
        $owner = $this->makeTeacher();

        $otherTeacher = $this->makeTeacher();

        $plan = $this->makePlan();

        $subscription =
            $this->makePendingSubscription(
                $owner,
                $plan
            );

        $response = $this
            ->actingAs($otherTeacher)
            ->get(
                route(
                    'teacher.payment.create',
                    $subscription
                )
            );

        $response->assertForbidden();
    }

    public function test_admin_can_approve_pending_payment_and_activate_subscription(): void
    {
        $admin = $this->makeAdmin();

        $teacher = $this->makeTeacher();

        $plan = $this->makePlan([
            'duration_days' => 30,
            'application_limit' => 12,
        ]);

        $subscription =
            $this->makePendingSubscription(
                $teacher,
                $plan
            );

        $payment =
            $this->makePendingPayment(
                $teacher,
                $subscription
            );

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.payments.approve',
                    $payment
                )
            );

        $response->assertSessionHasNoErrors();

        $subscription->refresh();
        $payment->refresh();

        $this->assertSame(
            'active',
            $subscription->status
        );

        $this->assertNotNull(
            $subscription->starts_at
        );

        $this->assertNotNull(
            $subscription->expires_at
        );

        $this->assertSame(
            0,
            $subscription->applications_used
        );

        $this->assertNull(
            $subscription->pending_user_id
        );

        $this->assertSame(
            'paid',
            $payment->status
        );

        $this->assertNotNull(
            $payment->paid_at
        );

        $this->assertNull(
            $payment->pending_subscription_id
        );
    }

    public function test_payment_approval_uses_subscription_duration_snapshot(): void
    {
        $admin = $this->makeAdmin();

        $teacher = $this->makeTeacher();

        $plan = $this->makePlan([
            'duration_days' => 30,
        ]);

        $subscription =
            $this->makePendingSubscription(
                $teacher,
                $plan,
                [
                    'duration_days_snapshot' => 45,
                ]
            );

        /*
         * Change the live plan after the pending
         * subscription has captured its snapshot.
         */
        $plan->update([
            'duration_days' => 365,
        ]);

        $payment =
            $this->makePendingPayment(
                $teacher,
                $subscription
            );

        $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.payments.approve',
                    $payment
                )
            )
            ->assertSessionHasNoErrors();

        $subscription->refresh();

        $this->assertEquals(
            45,
            $subscription
                ->starts_at
                ->diffInDays(
                    $subscription->expires_at
                )
        );
    }

    public function test_approving_new_subscription_cancels_existing_active_subscription(): void
    {
        $admin = $this->makeAdmin();

        $teacher = $this->makeTeacher();

        $oldPlan = $this->makePlan([
            'name' => 'Old Plan',
        ]);

        $newPlan = $this->makePlan([
            'name' => 'New Plan',
        ]);

        $oldSubscription =
            $this->makeActiveSubscription(
                $teacher,
                $oldPlan
            );

        $newSubscription =
            $this->makePendingSubscription(
                $teacher,
                $newPlan
            );

        $payment =
            $this->makePendingPayment(
                $teacher,
                $newSubscription
            );

        $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.payments.approve',
                    $payment
                )
            )
            ->assertSessionHasNoErrors();

        $oldSubscription->refresh();
        $newSubscription->refresh();

        $this->assertSame(
            'cancelled',
            $oldSubscription->status
        );

        $this->assertSame(
            'active',
            $newSubscription->status
        );
    }

    public function test_admin_can_reject_pending_payment(): void
    {
        $admin = $this->makeAdmin();

        $teacher = $this->makeTeacher();

        $plan = $this->makePlan();

        $subscription =
            $this->makePendingSubscription(
                $teacher,
                $plan
            );

        $payment =
            $this->makePendingPayment(
                $teacher,
                $subscription
            );

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.payments.reject',
                    $payment
                )
            );

        $response->assertSessionHasNoErrors();

        $subscription->refresh();
        $payment->refresh();

        $this->assertSame(
            'failed',
            $payment->status
        );

        $this->assertNull(
            $payment->pending_subscription_id
        );

        $this->assertSame(
            'cancelled',
            $subscription->status
        );

        $this->assertNull(
            $subscription->pending_user_id
        );
    }

    public function test_paid_payment_cannot_be_approved_again(): void
    {
        $admin = $this->makeAdmin();

        $teacher = $this->makeTeacher();

        $plan = $this->makePlan();

        $subscription =
            $this->makeActiveSubscription(
                $teacher,
                $plan
            );

        $payment = Payment::query()->create([
            'user_id' => $teacher->id,

            'subscription_id' =>
                $subscription->id,

            'transaction_id' =>
                'TXN-PAID-001',

            'payment_method' => 'bkash',

            'amount' =>
                $subscription->amount,

            'currency' => 'BDT',

            'status' => 'paid',

            'gateway_response' => null,

            'paid_at' => now(),

            'pending_subscription_id' => null,
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.payments.approve',
                    $payment
                )
            );

        $response->assertSessionHas(
            'error'
        );

        $this->assertSame(
            'paid',
            $payment->fresh()->status
        );
    }

    public function test_failed_payment_cannot_be_rejected_again(): void
    {
        $admin = $this->makeAdmin();

        $teacher = $this->makeTeacher();

        $plan = $this->makePlan();

        $subscription =
            $this->makePendingSubscription(
                $teacher,
                $plan
            );

        $payment = Payment::query()->create([
            'user_id' => $teacher->id,

            'subscription_id' =>
                $subscription->id,

            'transaction_id' =>
                'TXN-FAILED-001',

            'payment_method' => 'bkash',

            'amount' =>
                $subscription->amount,

            'currency' => 'BDT',

            'status' => 'failed',

            'gateway_response' => null,

            'paid_at' => null,

            'pending_subscription_id' => null,
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.payments.reject',
                    $payment
                )
            );

        $response->assertSessionHas(
            'error'
        );

        $this->assertSame(
            'failed',
            $payment->fresh()->status
        );

        $this->assertSame(
            'pending',
            $subscription->fresh()->status
        );
    }

    public function test_payment_transaction_id_must_be_unique_at_database_level(): void
    {
        /*
         * Two different teachers are intentionally used here.
         *
         * Each teacher is allowed one pending subscription,
         * so the subscription pending_user_id unique guard
         * does not interfere with the transaction ID test.
         */

        $teacherOne =
            $this->makeTeacher();

        $teacherTwo =
            $this->makeTeacher();

        $planOne =
            $this->makePlan();

        $planTwo =
            $this->makePlan();

        $subscriptionOne =
            $this->makePendingSubscription(
                $teacherOne,
                $planOne
            );

        $subscriptionTwo =
            $this->makePendingSubscription(
                $teacherTwo,
                $planTwo
            );

        Payment::query()->create([
            'user_id' =>
                $teacherOne->id,

            'subscription_id' =>
                $subscriptionOne->id,

            'transaction_id' =>
                'DUPLICATE-TXN-001',

            'payment_method' =>
                'bkash',

            'amount' =>
                $subscriptionOne->amount,

            'currency' =>
                'BDT',

            'status' =>
                'failed',

            'gateway_response' =>
                null,

            'paid_at' =>
                null,

            'pending_subscription_id' =>
                null,
        ]);

        $this->expectException(
            UniqueConstraintViolationException::class
        );

        Payment::query()->create([
            'user_id' =>
                $teacherTwo->id,

            'subscription_id' =>
                $subscriptionTwo->id,

            'transaction_id' =>
                'DUPLICATE-TXN-001',

            'payment_method' =>
                'nagad',

            'amount' =>
                $subscriptionTwo->amount,

            'currency' =>
                'BDT',

            'status' =>
                'pending',

            'gateway_response' =>
                null,

            'paid_at' =>
                null,

            'pending_subscription_id' =>
                $subscriptionTwo->id,
        ]);
    }
}