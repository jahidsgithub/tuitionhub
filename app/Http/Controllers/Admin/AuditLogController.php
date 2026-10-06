<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(
        Request $request
    ): View {
        $query = AuditLog::query()
            ->with('actor');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim(
                $request->search
            );

            $query->where(
                function ($q) use ($search) {
                    $q->where(
                        'action',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhere(
                            'route_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'target_type',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'target_id',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'actor',
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

        /*
        |--------------------------------------------------------------------------
        | Admin Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('admin')) {
            $query->where(
                'actor_user_id',
                $request->admin
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Method Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('method')) {
            $query->where(
                'method',
                strtoupper(
                    $request->method
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Action Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('action')) {
            $query->where(
                'action',
                $request->action
            );
        }

        $logs = $query
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        $admins = User::query()
            ->where(
                'role',
                'admin'
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);

        $actions = AuditLog::query()
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $totalLogs =
            AuditLog::count();

        $todayLogs =
            AuditLog::query()
                ->whereDate(
                    'created_at',
                    today()
                )
                ->count();

        $recentAdminCount =
            AuditLog::query()
                ->where(
                    'created_at',
                    '>=',
                    now()->subDays(7)
                )
                ->whereNotNull(
                    'actor_user_id'
                )
                ->distinct(
                    'actor_user_id'
                )
                ->count(
                    'actor_user_id'
                );

        return view(
            'admin.audit-logs.index',
            compact(
                'logs',
                'admins',
                'actions',
                'totalLogs',
                'todayLogs',
                'recentAdminCount'
            )
        );
    }
}