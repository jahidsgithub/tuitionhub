<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (! $user) {
            return redirect()
                ->route('login');
        }

        if ($user->status === 'active') {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Immediately terminate session
        |--------------------------------------------------------------------------
        */

        Auth::guard('web')->logout();

        $request
            ->session()
            ->invalidate();

        $request
            ->session()
            ->regenerateToken();

        $message = match ($user->status) {
            'suspended' =>
                'Your account has been suspended. Please contact support.',

            'inactive' =>
                'Your account is currently inactive.',

            default =>
                'Your account is not currently available.',
        };

        return redirect()
            ->route('login')
            ->withErrors([
                'email' => $message,
            ]);
    }
}