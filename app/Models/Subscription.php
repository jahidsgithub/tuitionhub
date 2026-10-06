<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'plan_name_snapshot',
        'amount',
        'duration_days_snapshot',
        'application_limit_snapshot',
        'plan_snapshot_captured_at',
        'starts_at',
        'expires_at',
        'status',
        'applications_used',
        'pending_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' =>
                'decimal:2',

            'duration_days_snapshot' =>
                'integer',

            'application_limit_snapshot' =>
                'integer',

            'plan_snapshot_captured_at' =>
                'datetime',

            'starts_at' =>
                'datetime',

            'expires_at' =>
                'datetime',

            'applications_used' =>
                'integer',

            'pending_user_id' =>
                'integer',
        ];
    }

    protected static function booted(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Capture Plan Snapshot on Creation
        |--------------------------------------------------------------------------
        */

        static::creating(
            function (
                Subscription $subscription
            ) {
                if (
                    ! $subscription
                        ->plan_snapshot_captured_at
                ) {
                    $plan =
                        SubscriptionPlan::query()
                            ->find(
                                $subscription
                                    ->subscription_plan_id
                            );

                    if ($plan) {
                        if (
                            blank(
                                $subscription
                                    ->plan_name_snapshot
                            )
                        ) {
                            $subscription
                                ->plan_name_snapshot =
                                $plan->name;
                        }

                        if (
                            $subscription
                                ->duration_days_snapshot ===
                            null
                        ) {
                            $subscription
                                ->duration_days_snapshot =
                                $plan->duration_days;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | NULL application_limit is valid and means unlimited.
                        |--------------------------------------------------------------------------
                        */

                        if (
                            ! array_key_exists(
                                'application_limit_snapshot',
                                $subscription->getAttributes()
                            )
                        ) {
                            $subscription
                                ->application_limit_snapshot =
                                $plan->application_limit;
                        }

                        $subscription
                            ->plan_snapshot_captured_at =
                            now();
                    }
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Pending Subscription DB Guard
        |--------------------------------------------------------------------------
        */

        static::saving(
            function (
                Subscription $subscription
            ) {
                if (
                    $subscription->status ===
                    'pending'
                ) {
                    $subscription
                        ->pending_user_id =
                        $subscription->user_id;
                } else {
                    $subscription
                        ->pending_user_id =
                        null;
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(
            SubscriptionPlan::class,
            'subscription_plan_id'
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            Payment::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Subscription State
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        if (
            $this->status !==
            'active'
        ) {
            return false;
        }

        if (
            $this->expires_at ===
            null
        ) {
            return true;
        }

        return $this
            ->expires_at
            ->isFuture();
    }

    /*
    |--------------------------------------------------------------------------
    | Snapshot Helpers
    |--------------------------------------------------------------------------
    */

    public function snapshotPlanName(): string
    {
        if (
            $this->plan_snapshot_captured_at
        ) {
            return $this
                ->plan_name_snapshot
                ?: 'Subscription';
        }

        return $this
            ->plan_name_snapshot
            ?: $this
                ->plan
                ?->name
            ?: 'Subscription';
    }

    public function snapshotDurationDays(): ?int
    {
        /*
        |--------------------------------------------------------------------------
        | Captured Snapshot Is Authoritative
        |--------------------------------------------------------------------------
        */

        if (
            $this->plan_snapshot_captured_at
        ) {
            return $this
                ->duration_days_snapshot !==
                null
                ? (int) $this
                    ->duration_days_snapshot
                : null;
        }

        /*
        |--------------------------------------------------------------------------
        | Legacy Fallback Only
        |--------------------------------------------------------------------------
        */

        if (
            $this->duration_days_snapshot !==
            null
        ) {
            return (int)
                $this
                    ->duration_days_snapshot;
        }

        if (
            $this->plan &&
            $this->plan
                ->duration_days !==
                null
        ) {
            return (int)
                $this->plan
                    ->duration_days;
        }

        return null;
    }

    public function snapshotApplicationLimit(): ?int
    {
        /*
        |--------------------------------------------------------------------------
        | Captured NULL = Unlimited
        |--------------------------------------------------------------------------
        |
        | Once snapshot_captured_at exists, NEVER fall back to the live plan.
        |
        */

        if (
            $this->plan_snapshot_captured_at
        ) {
            return $this
                ->application_limit_snapshot !==
                null
                ? (int) $this
                    ->application_limit_snapshot
                : null;
        }

        /*
        |--------------------------------------------------------------------------
        | Legacy Subscription Fallback
        |--------------------------------------------------------------------------
        */

        if (
            $this->application_limit_snapshot !==
            null
        ) {
            return (int)
                $this
                    ->application_limit_snapshot;
        }

        if (
            $this->plan &&
            $this->plan
                ->application_limit !==
                null
        ) {
            return (int)
                $this->plan
                    ->application_limit;
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Application Limit Helpers
    |--------------------------------------------------------------------------
    */

    public function hasApplicationLimit(): bool
    {
        return $this
            ->snapshotApplicationLimit() !==
            null;
    }

    public function canApply(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        $limit =
            $this
                ->snapshotApplicationLimit();

        if ($limit === null) {
            return true;
        }

        return
            $this->applications_used <
            $limit;
    }

    public function remainingApplications(): ?int
    {
        $limit =
            $this
                ->snapshotApplicationLimit();

        if ($limit === null) {
            return null;
        }

        return max(
            0,
            $limit -
            $this->applications_used
        );
    }
}