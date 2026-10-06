<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Backfill Existing Subscription Snapshots
        |--------------------------------------------------------------------------
        |
        | Existing subscriptions that were created before snapshot columns were
        | introduced may still contain NULL snapshot values.
        |
        | We freeze the current plan values into those historical subscriptions.
        |
        */

        DB::table('subscriptions')
            ->orderBy('id')
            ->chunkById(
                100,
                function ($subscriptions) {
                    foreach ($subscriptions as $subscription) {
                        /*
                        |--------------------------------------------------------------------------
                        | Skip Rows Already Fully Snapshotted
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $subscription->plan_name_snapshot !== null &&
                            $subscription->duration_days_snapshot !== null &&
                            $subscription->application_limit_snapshot !== null
                        ) {
                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Load Current Plan
                        |--------------------------------------------------------------------------
                        */

                        $plan = DB::table('subscription_plans')
                            ->where(
                                'id',
                                $subscription->subscription_plan_id
                            )
                            ->first();

                        /*
                        |--------------------------------------------------------------------------
                        | Missing Plan
                        |--------------------------------------------------------------------------
                        |
                        | Historical subscription may reference a plan that no longer
                        | exists. In that case we preserve any snapshot values already
                        | present and avoid fabricating data.
                        |
                        */

                        if (! $plan) {
                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Backfill Only Missing Snapshot Fields
                        |--------------------------------------------------------------------------
                        */

                        DB::table('subscriptions')
                            ->where(
                                'id',
                                $subscription->id
                            )
                            ->update([
                                'plan_name_snapshot' =>
                                    $subscription->plan_name_snapshot
                                    ?? $plan->name,

                                'duration_days_snapshot' =>
                                    $subscription->duration_days_snapshot
                                    ?? $plan->duration_days,

                                'application_limit_snapshot' =>
                                    $subscription->application_limit_snapshot
                                    ?? $plan->application_limit,

                                'updated_at' =>
                                    now(),
                            ]);
                    }
                },
                'id'
            );
    }

    /**
     * Reverse the migrations.
     *
     * Historical snapshot data should not be erased on rollback.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Intentionally No-op
        |--------------------------------------------------------------------------
        |
        | Snapshot values represent historical billing data.
        | Removing them during rollback could destroy useful historical state.
        |
        */
    }
};