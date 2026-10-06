<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Show teachers and students.
     */
    public function index(Request $request): View
    {
        $query = User::query()
            ->with([
                'teacherProfile.subjects',
                'teacherProfile.locations',
                'studentProfile',
            ])
            ->whereIn('role', [
                'teacher',
                'student',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim(
                (string) $request->search
            );

            $query->where(
                function ($q) use ($search) {
                    $q->where(
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

        /*
        |--------------------------------------------------------------------------
        | Role Filter
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('role') &&
            in_array(
                $request->role,
                [
                    'teacher',
                    'student',
                ],
                true
            )
        ) {
            $query->where(
                'role',
                $request->role
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('status') &&
            in_array(
                $request->status,
                [
                    'active',
                    'inactive',
                    'suspended',
                ],
                true
            )
        ) {
            $query->where(
                'status',
                $request->status
            );
        }

        $users = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'admin.users.index',
            compact('users')
        );
    }

    /**
     * Activate user account.
     */
    public function activate(
        User $user
    ): RedirectResponse {
        $this->authorizeManagedUser(
            $user
        );

        $alreadyActive = false;

        $user = DB::transaction(
            function () use (
                $user,
                &$alreadyActive
            ) {
                $lockedUser = User::query()
                    ->whereKey(
                        $user->id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->authorizeManagedUser(
                    $lockedUser
                );

                if (
                    $lockedUser->status ===
                    'active'
                ) {
                    $alreadyActive = true;

                    return $lockedUser;
                }

                $lockedUser->update([
                    'status' => 'active',
                ]);

                return $lockedUser;
            }
        );

        if ($alreadyActive) {
            return back()->with(
                'error',
                'This account is already active.'
            );
        }

        UserNotificationService::send(
            $user,
            'Account Activated',
            'Your Tuition Hub account has been activated.',
            route('dashboard'),
            'account_status'
        );

        return back()->with(
            'success',
            $user->name
            .' account activated successfully.'
        );
    }

    /**
     * Mark user inactive.
     */
    public function inactive(
        User $user
    ): RedirectResponse {
        $this->authorizeManagedUser(
            $user
        );

        $alreadyInactive = false;

        $user = DB::transaction(
            function () use (
                $user,
                &$alreadyInactive
            ) {
                $lockedUser = User::query()
                    ->whereKey(
                        $user->id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->authorizeManagedUser(
                    $lockedUser
                );

                if (
                    $lockedUser->status ===
                    'inactive'
                ) {
                    $alreadyInactive = true;

                    return $lockedUser;
                }

                $lockedUser->update([
                    'status' => 'inactive',
                ]);

                return $lockedUser;
            }
        );

        if ($alreadyInactive) {
            return back()->with(
                'error',
                'This account is already inactive.'
            );
        }

        UserNotificationService::send(
            $user,
            'Account Status Updated',
            'Your Tuition Hub account has been marked as inactive.',
            null,
            'account_status'
        );

        return back()->with(
            'success',
            $user->name
            .' account marked as inactive.'
        );
    }

    /**
     * Suspend user.
     */
    public function suspend(
        User $user
    ): RedirectResponse {
        $this->authorizeManagedUser(
            $user
        );

        $alreadySuspended = false;

        $user = DB::transaction(
            function () use (
                $user,
                &$alreadySuspended
            ) {
                $lockedUser = User::query()
                    ->whereKey(
                        $user->id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->authorizeManagedUser(
                    $lockedUser
                );

                if (
                    $lockedUser->status ===
                    'suspended'
                ) {
                    $alreadySuspended = true;

                    return $lockedUser;
                }

                $lockedUser->update([
                    'status' => 'suspended',
                ]);

                return $lockedUser;
            }
        );

        if ($alreadySuspended) {
            return back()->with(
                'error',
                'This account is already suspended.'
            );
        }

        UserNotificationService::send(
            $user,
            'Account Suspended',
            'Your Tuition Hub account has been suspended by an administrator.',
            null,
            'account_status'
        );

        return back()->with(
            'success',
            $user->name
            .' account suspended successfully.'
        );
    }

    /**
     * Only teacher/student accounts can be managed here.
     */
    private function authorizeManagedUser(
        User $user
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Defensive Self Protection
        |--------------------------------------------------------------------------
        */

        abort_if(
            auth()->id() === $user->id,
            403,
            'You cannot change your own account status here.'
        );

        /*
        |--------------------------------------------------------------------------
        | Only Teacher / Student Accounts
        |--------------------------------------------------------------------------
        */

        abort_unless(
            in_array(
                $user->role,
                [
                    'teacher',
                    'student',
                ],
                true
            ),
            403,
            'Only teacher and student accounts can be managed here.'
        );
    }
}