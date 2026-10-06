<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $overrides = []): User
    {
        return User::factory()->create(
            array_merge([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '01700000001',
                'password' => Hash::make('Password@123'),
                'role' => 'teacher',
                'status' => 'active',
                'email_verified_at' => now(),
            ], $overrides)
        );
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_active_user_can_login_with_correct_credentials(): void
    {
        $user = $this->makeUser();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password@123',
        ]);

        $this->assertAuthenticatedAs($user);

        $response->assertRedirect();
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->from(route('login'))
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'WrongPassword@123',
            ]);

        $this->assertGuest();

        $response->assertRedirect(route('login'));
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = $this->makeUser([
            'status' => 'inactive',
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password@123',
        ]);

        $this->assertGuest();
    }

    public function test_suspended_user_cannot_login(): void
    {
        $user = $this->makeUser([
            'status' => 'suspended',
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password@123',
        ]);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->post(route('logout'));

        $this->assertGuest();

        $response->assertRedirect();
    }

    public function test_forgot_password_page_can_be_rendered(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $user = $this->makeUser();

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        Notification::assertSentTo(
            $user,
            ResetPassword::class
        );

        $response->assertSessionHasNoErrors();
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = $this->makeUser();

        $token = Password::broker()
            ->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertTrue(
            Hash::check(
                'NewPassword@123',
                $user->fresh()->password
            )
        );
    }

    public function test_email_verification_notice_is_accessible_to_unverified_authenticated_user(): void
    {
        $user = $this->makeUser([
            'email_verified_at' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('verification.notice'));

        $response->assertOk();
    }

    public function test_verification_email_can_be_resent(): void
    {
        Notification::fake();

        $user = $this->makeUser([
            'email_verified_at' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('verification.send'));

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );

        $response->assertSessionHasNoErrors();
    }

    public function test_verified_user_can_access_account_security_page(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->get(route('account.security.edit'));

        $response->assertOk();
    }

    public function test_guest_cannot_access_account_security_page(): void
    {
        $response = $this->get(
            route('account.security.edit')
        );

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->put(route('account.security.update'), [
                'action' => 'change_password',
                'current_password' => 'Password@123',
                'password' => 'ChangedPassword@123',
                'password_confirmation' => 'ChangedPassword@123',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertTrue(
            Hash::check(
                'ChangedPassword@123',
                $user->fresh()->password
            )
        );
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->from(route('account.security.edit'))
            ->put(route('account.security.update'), [
                'action' => 'change_password',
                'current_password' => 'WrongPassword@123',
                'password' => 'ChangedPassword@123',
                'password_confirmation' => 'ChangedPassword@123',
            ]);

        $response->assertSessionHasErrors();

        $this->assertTrue(
            Hash::check(
                'Password@123',
                $user->fresh()->password
            )
        );
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->from(route('account.security.edit'))
            ->put(route('account.security.update'), [
                'action' => 'change_password',
                'current_password' => 'Password@123',
                'password' => 'ChangedPassword@123',
                'password_confirmation' => 'DifferentPassword@123',
            ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_user_can_access_account_settings_page(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->get(route('account.settings.edit'));

        $response->assertOk();
    }

    public function test_guest_cannot_access_account_settings_page(): void
    {
        $response = $this->get(
            route('account.settings.edit')
        );

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_user_can_update_basic_account_information(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->put(route('account.settings.update'), [
                'name' => 'Updated User',
                'email' => $user->email,
                'phone' => '01700000009',
                'current_password' => 'Password@123',
            ]);

        $response->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame(
            'Updated User',
            $user->name
        );

        $this->assertSame(
            '01700000009',
            $user->phone
        );
    }

    public function test_changing_email_marks_email_as_unverified(): void
    {
        $user = $this->makeUser();

        $response = $this
            ->actingAs($user)
            ->put(route('account.settings.update'), [
                'name' => $user->name,
                'email' => 'changed@example.com',
                'phone' => $user->phone,
                'current_password' => 'Password@123',
            ]);

        $response->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame(
            'changed@example.com',
            $user->email
        );

        $this->assertNull(
            $user->email_verified_at
        );
    }
}