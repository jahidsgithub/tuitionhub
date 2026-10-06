<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuditAdminAction
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response = $next($request);

        /*
        |--------------------------------------------------------------------------
        | Only Log Successful Admin Write Actions
        |--------------------------------------------------------------------------
        */

        if (
            ! auth()->check() ||
            ! auth()->user()->isAdmin()
        ) {
            return $response;
        }

        if (
            in_array(
                strtoupper($request->method()),
                [
                    'GET',
                    'HEAD',
                    'OPTIONS',
                ],
                true
            )
        ) {
            return $response;
        }

        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $route = $request->route();

        if (! $route) {
            return $response;
        }

        $routeName = $route->getName();

        /*
        |--------------------------------------------------------------------------
        | Do Not Audit Audit-Log Viewing
        |--------------------------------------------------------------------------
        */

        if (
            $routeName &&
            str_starts_with(
                $routeName,
                'admin.audit-logs.'
            )
        ) {
            return $response;
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Target
        |--------------------------------------------------------------------------
        */

        [$targetType, $targetId] =
            $this->resolveTarget(
                $request
            );

        /*
        |--------------------------------------------------------------------------
        | Safe Metadata Only
        |--------------------------------------------------------------------------
        */

        $metadata = $this->safeMetadata(
            $request
        );

        AuditLog::create([
            'actor_user_id' =>
                auth()->id(),

            'action' =>
                $this->resolveAction(
                    $routeName,
                    $request->method()
                ),

            'route_name' =>
                $routeName,

            'method' =>
                strtoupper(
                    $request->method()
                ),

            'target_type' =>
                $targetType,

            'target_id' =>
                $targetId,

            'metadata' =>
                empty($metadata)
                    ? null
                    : $metadata,

            'created_at' =>
                now(),
        ]);

        return $response;
    }

    private function resolveAction(
        ?string $routeName,
        string $method
    ): string {
        if ($routeName) {
            return Str::of($routeName)
                ->replace('admin.', '')
                ->replace('.', ' ')
                ->headline()
                ->toString();
        }

        return Str::headline(
            strtolower($method)
        );
    }

    private function resolveTarget(
        Request $request
    ): array {
        $parameters = $request
            ->route()
            ?->parameters() ?? [];

        foreach (
            $parameters as $key => $value
        ) {
            if (is_object($value)) {
                $targetId =
                    method_exists(
                        $value,
                        'getKey'
                    )
                        ? $value->getKey()
                        : null;

                return [
                    class_basename($value),
                    $targetId !== null
                        ? (string) $targetId
                        : null,
                ];
            }

            if (
                is_scalar($value) &&
                $value !== ''
            ) {
                return [
                    Str::headline($key),
                    (string) $value,
                ];
            }
        }

        return [
            null,
            null,
        ];
    }

    private function safeMetadata(
        Request $request
    ): array {
        $allowedKeys = [
            'status',
            'role',
            'is_verified',
            'is_available',
            'name',
            'slug',
            'price',
            'duration_days',
            'application_limit',
            'is_featured',
        ];

        $metadata = [];

        foreach ($allowedKeys as $key) {
            if (! $request->exists($key)) {
                continue;
            }

            $value = $request->input($key);

            if (
                is_string($value) &&
                mb_strlen($value) > 255
            ) {
                $value =
                    mb_substr(
                        $value,
                        0,
                        255
                    );
            }

            if (
                is_scalar($value) ||
                is_null($value)
            ) {
                $metadata[$key] =
                    $value;
            }
        }

        return $metadata;
    }
}