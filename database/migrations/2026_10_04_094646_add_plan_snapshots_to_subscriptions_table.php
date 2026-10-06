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
            $table->string('plan_name_snapshot')
                ->nullable()
                ->after('subscription_plan_id');

            $table->unsignedInteger('duration_days_snapshot')
                ->nullable()
                ->after('amount');

            $table->unsignedInteger('application_limit_snapshot')
                ->nullable()
                ->after('duration_days_snapshot');
        });

        /*
        |--------------------------------------------------------------------------
        | Backfill Existing Subscriptions
        |--------------------------------------------------------------------------
        |
        | Existing records were created before snapshot support.
        | Copy their current plan values once.
        |
        */

        DB::table('subscriptions')
            ->orderBy('id')
            ->chunkById(
                100,
                function ($subscriptions) {
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
                                    $plan->name,

                                'duration_days_snapshot' =>
                                    $plan->duration_days,

                                'application_limit_snapshot' =>
                                    $plan->application_limit,
                            ]);
                    }
                }
            );
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'plan_name_snapshot',
                'duration_days_snapshot',
                'application_limit_snapshot',
            ]);
        });
    }
};