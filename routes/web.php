<?php

use App\Http\Controllers\AccountSecurityController;
use App\Http\Controllers\AccountSettingsController;

use App\Http\Controllers\Admin\AssignmentController as AdminAssignmentController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\TeacherVerificationController;
use App\Http\Controllers\Admin\TuitionModerationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VerificationDocumentController as AdminVerificationDocumentController;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\ResetPasswordController;

use App\Http\Controllers\NotificationController;

use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\ComplaintController as StudentComplaintController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\TeacherController as StudentTeacherController;
use App\Http\Controllers\Student\TeacherReviewController;
use App\Http\Controllers\Student\TuitionApplicationController;
use App\Http\Controllers\Student\TuitionPostController;

use App\Http\Controllers\Teacher\AssignmentController as TeacherAssignmentController;
use App\Http\Controllers\Teacher\ComplaintController as TeacherComplaintController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\PaymentController as TeacherPaymentController;
use App\Http\Controllers\Teacher\ProfileController as TeacherProfileController;
use App\Http\Controllers\Teacher\SubscriptionController;
use App\Http\Controllers\Teacher\TeacherRequestController;
use App\Http\Controllers\Teacher\TuitionController as TeacherTuitionController;
use App\Http\Controllers\Teacher\VerificationDocumentController as TeacherVerificationDocumentController;

use App\Http\Middleware\EnsureAccountIsActive;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Guest Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/register', [
        RegisteredUserController::class,
        'create',
    ])->name('register');

    Route::post('/register', [
        RegisteredUserController::class,
        'store',
    ]);

    Route::get('/login', [
        AuthenticatedSessionController::class,
        'create',
    ])->name('login');

    Route::post('/login', [
        AuthenticatedSessionController::class,
        'store',
    ]);

    Route::get('/forgot-password', [
        ForgotPasswordController::class,
        'create',
    ])->name('password.request');

    Route::post('/forgot-password', [
        ForgotPasswordController::class,
        'store',
    ])->name('password.email');

    Route::get('/reset-password/{token}', [
        ResetPasswordController::class,
        'create',
    ])->name('password.reset');

    Route::post('/reset-password', [
        ResetPasswordController::class,
        'store',
    ])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
|
| Logout is intentionally outside auth.session and active-account protection.
| A blocked account must still be able to terminate its current session.
|
*/

Route::post('/logout', [
    AuthenticatedSessionController::class,
    'destroy',
])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Active Authenticated Account Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'auth.session',
    EnsureAccountIsActive::class,
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Email Verification
    |--------------------------------------------------------------------------
    */

    Route::get('/verify-email', [
        EmailVerificationController::class,
        'notice',
    ])->name('verification.notice');

    Route::get('/verify-email/{id}/{hash}', [
        EmailVerificationController::class,
        'verify',
    ])
        ->middleware([
            'signed',
            'throttle:6,1',
        ])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [
        EmailVerificationController::class,
        'resend',
    ])
        ->middleware(
            'throttle:6,1'
        )
        ->name('verification.send');

    /*
    |--------------------------------------------------------------------------
    | Account Settings
    |--------------------------------------------------------------------------
    */

    Route::get('/account/settings', [
        AccountSettingsController::class,
        'edit',
    ])->name('account.settings.edit');

    Route::put('/account/settings', [
        AccountSettingsController::class,
        'update',
    ])->name('account.settings.update');

    /*
    |--------------------------------------------------------------------------
    | Account Security
    |--------------------------------------------------------------------------
    */

    Route::get('/account/security', [
        AccountSecurityController::class,
        'edit',
    ])->name('account.security.edit');

    Route::put('/account/security/password', [
        AccountSecurityController::class,
        'update',
    ])->name('account.security.update');

    /*
    |--------------------------------------------------------------------------
    | Dashboard Redirect
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {

        $user = auth()->user();

        if (
            ! $user->isAdmin() &&
            ! $user->hasVerifiedEmail()
        ) {
            return redirect()
                ->route(
                    'verification.notice'
                );
        }

        if ($user->isAdmin()) {
            return redirect()
                ->route(
                    'admin.dashboard'
                );
        }

        if ($user->isTeacher()) {
            return redirect()
                ->route(
                    'teacher.dashboard'
                );
        }

        if ($user->isStudent()) {
            return redirect()
                ->route(
                    'student.dashboard'
                );
        }

        abort(403);

    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::get('/notifications', [
        NotificationController::class,
        'index',
    ])->name('notifications.index');

    Route::patch('/notifications/read-all', [
        NotificationController::class,
        'markAllRead',
    ])->name('notifications.read-all');

    Route::patch('/notifications/{notification}/read', [
        NotificationController::class,
        'markRead',
    ])->name('notifications.read');

    Route::delete('/notifications/{notification}', [
        NotificationController::class,
        'destroy',
    ])->name('notifications.destroy');
});

/*
|--------------------------------------------------------------------------
| Teacher
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'auth.session',
    EnsureAccountIsActive::class,
    'verified',
    'role:teacher',
])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {

        Route::get('/dashboard', [
            TeacherDashboardController::class,
            'index',
        ])->name('dashboard');

        Route::get('/profile', [
            TeacherProfileController::class,
            'edit',
        ])->name('profile.edit');

        Route::put('/profile', [
            TeacherProfileController::class,
            'update',
        ])->name('profile.update');

        /*
        |--------------------------------------------------------------------------
        | Verification Documents
        |--------------------------------------------------------------------------
        */

        Route::get('/verification-documents', [
            TeacherVerificationDocumentController::class,
            'index',
        ])->name('verification-documents.index');

        Route::post('/verification-documents', [
            TeacherVerificationDocumentController::class,
            'store',
        ])->name('verification-documents.store');

        Route::get('/verification-documents/{document}/view', [
            TeacherVerificationDocumentController::class,
            'view',
        ])->name('verification-documents.view');

        Route::delete('/verification-documents/{document}', [
            TeacherVerificationDocumentController::class,
            'destroy',
        ])->name('verification-documents.destroy');

        /*
        |--------------------------------------------------------------------------
        | Assignments
        |--------------------------------------------------------------------------
        */

        Route::get('/assignments', [
            TeacherAssignmentController::class,
            'index',
        ])->name('assignments.index');

        /*
        |--------------------------------------------------------------------------
        | Complaints
        |--------------------------------------------------------------------------
        */

        Route::get('/complaints', [
            TeacherComplaintController::class,
            'index',
        ])->name('complaints.index');

        Route::get('/assignments/{assignment}/complaint', [
            TeacherComplaintController::class,
            'create',
        ])->name('complaints.create');

        Route::post('/assignments/{assignment}/complaint', [
            TeacherComplaintController::class,
            'store',
        ])->name('complaints.store');

        /*
        |--------------------------------------------------------------------------
        | Subscription
        |--------------------------------------------------------------------------
        */

        Route::get('/subscription', [
            SubscriptionController::class,
            'index',
        ])->name('subscription.index');

        Route::post('/subscription/{plan}', [
            SubscriptionController::class,
            'subscribe',
        ])->name('subscription.subscribe');

        /*
        |--------------------------------------------------------------------------
        | Payment
        |--------------------------------------------------------------------------
        */

        Route::get('/payment/{subscription}', [
            TeacherPaymentController::class,
            'create',
        ])->name('payment.create');

        Route::post('/payment/{subscription}', [
            TeacherPaymentController::class,
            'store',
        ])->name('payment.store');

        /*
        |--------------------------------------------------------------------------
        | Direct Teacher Requests
        |--------------------------------------------------------------------------
        */

        Route::get('/requests', [
            TeacherRequestController::class,
            'index',
        ])->name('requests.index');

        Route::patch('/requests/{teacherRequest}/accept', [
            TeacherRequestController::class,
            'accept',
        ])->name('requests.accept');

        Route::patch('/requests/{teacherRequest}/reject', [
            TeacherRequestController::class,
            'reject',
        ])->name('requests.reject');

        /*
        |--------------------------------------------------------------------------
        | Tuition Marketplace
        |--------------------------------------------------------------------------
        */

        Route::get('/tuitions', [
            TeacherTuitionController::class,
            'index',
        ])->name('tuitions.index');

        /*
        |--------------------------------------------------------------------------
        | Keep before /tuitions/{tuition}
        |--------------------------------------------------------------------------
        */

        Route::get('/tuitions/applications', [
            TeacherTuitionController::class,
            'applications',
        ])->name('tuitions.applications');

        Route::get('/tuitions/{tuition}', [
            TeacherTuitionController::class,
            'show',
        ])->name('tuitions.show');

        Route::post('/tuitions/{tuition}/apply', [
            TeacherTuitionController::class,
            'apply',
        ])->name('tuitions.apply');

        Route::patch('/applications/{application}/withdraw', [
            TeacherTuitionController::class,
            'withdraw',
        ])->name('applications.withdraw');
    });

/*
|--------------------------------------------------------------------------
| Student / Guardian
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'auth.session',
    EnsureAccountIsActive::class,
    'verified',
    'role:student',
])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        Route::get('/dashboard', [
            StudentDashboardController::class,
            'index',
        ])->name('dashboard');

        Route::get('/profile', [
            StudentProfileController::class,
            'edit',
        ])->name('profile.edit');

        Route::put('/profile', [
            StudentProfileController::class,
            'update',
        ])->name('profile.update');

        /*
        |--------------------------------------------------------------------------
        | Assignments
        |--------------------------------------------------------------------------
        */

        Route::get('/assignments', [
            StudentAssignmentController::class,
            'index',
        ])->name('assignments.index');

        Route::patch('/assignments/{assignment}/complete', [
            StudentAssignmentController::class,
            'complete',
        ])->name('assignments.complete');

        Route::patch('/assignments/{assignment}/cancel', [
            StudentAssignmentController::class,
            'cancel',
        ])->name('assignments.cancel');

        /*
        |--------------------------------------------------------------------------
        | Review
        |--------------------------------------------------------------------------
        */

        Route::post('/assignments/{assignment}/review', [
            TeacherReviewController::class,
            'store',
        ])->name('assignments.review.store');

        /*
        |--------------------------------------------------------------------------
        | Complaints
        |--------------------------------------------------------------------------
        */

        Route::get('/complaints', [
            StudentComplaintController::class,
            'index',
        ])->name('complaints.index');

        Route::get('/assignments/{assignment}/complaint', [
            StudentComplaintController::class,
            'create',
        ])->name('complaints.create');

        Route::post('/assignments/{assignment}/complaint', [
            StudentComplaintController::class,
            'store',
        ])->name('complaints.store');

        /*
        |--------------------------------------------------------------------------
        | Find Teachers
        |--------------------------------------------------------------------------
        */

        Route::get('/teachers', [
            StudentTeacherController::class,
            'index',
        ])->name('teachers.index');

        Route::get('/teachers/{teacher}', [
            StudentTeacherController::class,
            'show',
        ])->name('teachers.show');

        Route::post('/teachers/{teacher}/request', [
            StudentTeacherController::class,
            'requestTeacher',
        ])->name('teachers.request');

        /*
        |--------------------------------------------------------------------------
        | Teacher Requests
        |--------------------------------------------------------------------------
        */

        Route::get('/teacher-requests', [
            StudentTeacherController::class,
            'requests',
        ])->name('teacher-requests.index');

        Route::patch('/teacher-requests/{teacherRequest}/cancel', [
            StudentTeacherController::class,
            'cancelRequest',
        ])->name('teacher-requests.cancel');

        Route::patch('/teacher-requests/{teacherRequest}/confirm', [
            StudentTeacherController::class,
            'confirmTeacher',
        ])->name('teacher-requests.confirm');

        /*
        |--------------------------------------------------------------------------
        | Tuition Posts
        |--------------------------------------------------------------------------
        */

        Route::get('/tuitions', [
            TuitionPostController::class,
            'index',
        ])->name('tuitions.index');

        Route::get('/tuitions/create', [
            TuitionPostController::class,
            'create',
        ])->name('tuitions.create');

        Route::post('/tuitions', [
            TuitionPostController::class,
            'store',
        ])->name('tuitions.store');

        Route::get('/tuitions/{tuition}/edit', [
            TuitionPostController::class,
            'edit',
        ])->name('tuitions.edit');

        Route::put('/tuitions/{tuition}', [
            TuitionPostController::class,
            'update',
        ])->name('tuitions.update');

        Route::patch('/tuitions/{tuition}/close', [
            TuitionPostController::class,
            'close',
        ])->name('tuitions.close');

        Route::delete('/tuitions/{tuition}', [
            TuitionPostController::class,
            'destroy',
        ])->name('tuitions.destroy');

        /*
        |--------------------------------------------------------------------------
        | Tuition Applications
        |--------------------------------------------------------------------------
        */

        Route::get('/tuitions/{tuition}/applications', [
            TuitionApplicationController::class,
            'index',
        ])->name('tuitions.applications.index');

        Route::patch('/tuitions/{tuition}/applications/{application}/shortlist', [
            TuitionApplicationController::class,
            'shortlist',
        ])->name('tuitions.applications.shortlist');

        Route::patch('/tuitions/{tuition}/applications/{application}/accept', [
            TuitionApplicationController::class,
            'accept',
        ])->name('tuitions.applications.accept');

        Route::patch('/tuitions/{tuition}/applications/{application}/reject', [
            TuitionApplicationController::class,
            'reject',
        ])->name('tuitions.applications.reject');
    });

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'auth.session',
    EnsureAccountIsActive::class,
    'role:admin',
    'admin.audit',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [
            AdminDashboardController::class,
            'index',
        ])->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Route::get('/users', [
            UserManagementController::class,
            'index',
        ])->name('users.index');

        Route::patch('/users/{user}/activate', [
            UserManagementController::class,
            'activate',
        ])->name('users.activate');

        Route::patch('/users/{user}/inactive', [
            UserManagementController::class,
            'inactive',
        ])->name('users.inactive');

        Route::patch('/users/{user}/suspend', [
            UserManagementController::class,
            'suspend',
        ])->name('users.suspend');

        /*
        |--------------------------------------------------------------------------
        | Teacher Verification
        |--------------------------------------------------------------------------
        */

        Route::get('/teachers', [
            TeacherVerificationController::class,
            'index',
        ])->name('teachers.index');

        Route::patch('/teachers/{teacher}/verify', [
            TeacherVerificationController::class,
            'verify',
        ])->name('teachers.verify');

        Route::patch('/teachers/{teacher}/unverify', [
            TeacherVerificationController::class,
            'unverify',
        ])->name('teachers.unverify');

        /*
        |--------------------------------------------------------------------------
        | Verification Documents
        |--------------------------------------------------------------------------
        */

        Route::get('/verification-documents', [
            AdminVerificationDocumentController::class,
            'index',
        ])->name('verification-documents.index');

        Route::get('/verification-documents/{document}/view', [
            AdminVerificationDocumentController::class,
            'view',
        ])->name('verification-documents.view');

        Route::patch('/verification-documents/{document}/approve', [
            AdminVerificationDocumentController::class,
            'approve',
        ])->name('verification-documents.approve');

        Route::patch('/verification-documents/{document}/reject', [
            AdminVerificationDocumentController::class,
            'reject',
        ])->name('verification-documents.reject');

        /*
        |--------------------------------------------------------------------------
        | Tuition Moderation
        |--------------------------------------------------------------------------
        */

        Route::get('/tuitions', [
            TuitionModerationController::class,
            'index',
        ])->name('tuitions.index');

        Route::patch('/tuitions/{tuition}/publish', [
            TuitionModerationController::class,
            'publish',
        ])->name('tuitions.publish');

        Route::patch('/tuitions/{tuition}/pending', [
            TuitionModerationController::class,
            'pending',
        ])->name('tuitions.pending');

        Route::patch('/tuitions/{tuition}/close', [
            TuitionModerationController::class,
            'close',
        ])->name('tuitions.close');

        /*
        |--------------------------------------------------------------------------
        | Assignments
        |--------------------------------------------------------------------------
        */

        Route::get('/assignments', [
            AdminAssignmentController::class,
            'index',
        ])->name('assignments.index');

        /*
        |--------------------------------------------------------------------------
        | Complaints
        |--------------------------------------------------------------------------
        */

        Route::get('/complaints', [
            AdminComplaintController::class,
            'index',
        ])->name('complaints.index');

        Route::patch('/complaints/{complaint}', [
            AdminComplaintController::class,
            'update',
        ])->name('complaints.update');

        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */

        Route::get('/payments', [
            AdminPaymentController::class,
            'index',
        ])->name('payments.index');

        Route::patch('/payments/{payment}/approve', [
            AdminPaymentController::class,
            'approve',
        ])->name('payments.approve');

        Route::patch('/payments/{payment}/reject', [
            AdminPaymentController::class,
            'reject',
        ])->name('payments.reject');

        /*
        |--------------------------------------------------------------------------
        | Payment Settings
        |--------------------------------------------------------------------------
        */

        Route::get('/payment-settings', [
            PaymentSettingController::class,
            'edit',
        ])->name('payment-settings.edit');

        Route::put('/payment-settings', [
            PaymentSettingController::class,
            'update',
        ])->name('payment-settings.update');

        /*
        |--------------------------------------------------------------------------
        | Subscription Plans
        |--------------------------------------------------------------------------
        */

        Route::get('/subscription-plans', [
            SubscriptionPlanController::class,
            'index',
        ])->name('subscription-plans.index');

        Route::get('/subscription-plans/{plan}/edit', [
            SubscriptionPlanController::class,
            'edit',
        ])->name('subscription-plans.edit');

        Route::put('/subscription-plans/{plan}', [
            SubscriptionPlanController::class,
            'update',
        ])->name('subscription-plans.update');

        Route::patch('/subscription-plans/{plan}/toggle', [
            SubscriptionPlanController::class,
            'toggle',
        ])->name('subscription-plans.toggle');

        /*
        |--------------------------------------------------------------------------
        | Audit Logs
        |--------------------------------------------------------------------------
        */

        Route::get('/audit-logs', [
            AuditLogController::class,
            'index',
        ])->name('audit-logs.index');
    });