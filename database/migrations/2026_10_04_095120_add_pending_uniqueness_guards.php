<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Remove Partial Columns From Previous Failed Attempt
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'subscriptions',
                'pending_user_id'
            )
        ) {
            Schema::table(
                'subscriptions',
                function (Blueprint $table) {
                    $table->dropColumn(
                        'pending_user_id'
                    );
                }
            );
        }

        if (
            Schema::hasColumn(
                'payments',
                'pending_subscription_id'
            )
        ) {
            Schema::table(
                'payments',
                function (Blueprint $table) {
                    $table->dropColumn(
                        'pending_subscription_id'
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Preflight: Duplicate Pending Subscriptions
        |--------------------------------------------------------------------------
        */

        $duplicateSubscriptions =
            DB::table('subscriptions')
                ->select(
                    'user_id',
                    DB::raw('COUNT(*) as total')
                )
                ->where(
                    'status',
                    'pending'
                )
                ->groupBy(
                    'user_id'
                )
                ->havingRaw(
                    'COUNT(*) > 1'
                )
                ->get();

        if (
            $duplicateSubscriptions
                ->isNotEmpty()
        ) {
            throw new \RuntimeException(
                'Migration stopped because duplicate pending subscriptions already exist.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Preflight: Duplicate Pending Payments
        |--------------------------------------------------------------------------
        */

        $duplicatePayments =
            DB::table('payments')
                ->select(
                    'subscription_id',
                    DB::raw('COUNT(*) as total')
                )
                ->whereNotNull(
                    'subscription_id'
                )
                ->where(
                    'status',
                    'pending'
                )
                ->groupBy(
                    'subscription_id'
                )
                ->havingRaw(
                    'COUNT(*) > 1'
                )
                ->get();

        if (
            $duplicatePayments
                ->isNotEmpty()
        ) {
            throw new \RuntimeException(
                'Migration stopped because duplicate pending payments already exist.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Add Normal Nullable Guard Columns
        |--------------------------------------------------------------------------
        */

        Schema::table(
            'subscriptions',
            function (Blueprint $table) {
                $table
                    ->unsignedBigInteger(
                        'pending_user_id'
                    )
                    ->nullable();

                $table->unique(
                    'pending_user_id',
                    'subscriptions_one_pending_per_user'
                );
            }
        );

        Schema::table(
            'payments',
            function (Blueprint $table) {
                $table
                    ->unsignedBigInteger(
                        'pending_subscription_id'
                    )
                    ->nullable();

                $table->unique(
                    'pending_subscription_id',
                    'payments_one_pending_per_subscription'
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Backfill Existing Pending Rows
        |--------------------------------------------------------------------------
        */

        DB::table('subscriptions')
            ->where(
                'status',
                'pending'
            )
            ->orderBy('id')
            ->eachById(
                function ($subscription) {
                    DB::table('subscriptions')
                        ->where(
                            'id',
                            $subscription->id
                        )
                        ->update([
                            'pending_user_id' =>
                                $subscription->user_id,
                        ]);
                }
            );

        DB::table('payments')
            ->where(
                'status',
                'pending'
            )
            ->whereNotNull(
                'subscription_id'
            )
            ->orderBy('id')
            ->eachById(
                function ($payment) {
                    DB::table('payments')
                        ->where(
                            'id',
                            $payment->id
                        )
                        ->update([
                            'pending_subscription_id' =>
                                $payment->subscription_id,
                        ]);
                }
            );
    }

    public function down(): void
    {
        if (
            Schema::hasColumn(
                'payments',
                'pending_subscription_id'
            )
        ) {
            Schema::table(
                'payments',
                function (Blueprint $table) {
                    $table->dropUnique(
                        'payments_one_pending_per_subscription'
                    );

                    $table->dropColumn(
                        'pending_subscription_id'
                    );
                }
            );
        }

        if (
            Schema::hasColumn(
                'subscriptions',
                'pending_user_id'
            )
        ) {
            Schema::table(
                'subscriptions',
                function (Blueprint $table) {
                    $table->dropUnique(
                        'subscriptions_one_pending_per_user'
                    );

                    $table->dropColumn(
                        'pending_user_id'
                    );
                }
            );
        }
    }
};