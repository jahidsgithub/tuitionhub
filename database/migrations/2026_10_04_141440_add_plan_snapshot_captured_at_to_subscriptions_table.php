<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('plan_snapshot_captured_at')
                ->nullable()
                ->after('application_limit_snapshot');
        });

        DB::table('subscriptions')
            ->orderBy('id')
            ->chunkById(100, function ($subscriptions) {
                foreach ($subscriptions as $subscription) {
                    $plan = DB::table('subscription_plans')
                        ->where(
                            'id',
                            $subscription->subscription_plan_id
                        )
                        ->first();

                    if (! $plan) {
                        continue;
                    }

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

                            /*
                            |--------------------------------------------------------------------------
                            | NULL is intentionally preserved for unlimited plans.
                            |--------------------------------------------------------------------------
                            */

                            'application_limit_snapshot' =>
                                $subscription->application_limit_snapshot
                                ?? $plan->application_limit,

                            'plan_snapshot_captured_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(
                'plan_snapshot_captured_at'
            );
        });
    }
};