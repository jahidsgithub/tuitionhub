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
        | Preflight Duplicate Check
        |--------------------------------------------------------------------------
        |
        | Same reporter + same assignment must not already have more than one
        | unresolved complaint.
        |
        */

        $duplicate = DB::table('complaints')
            ->select(
                'tuition_assignment_id',
                'reported_by',
                DB::raw('COUNT(*) as total')
            )
            ->whereIn(
                'status',
                [
                    'open',
                    'investigating',
                ]
            )
            ->groupBy(
                'tuition_assignment_id',
                'reported_by'
            )
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate) {
            throw new \RuntimeException(
                'Duplicate unresolved complaints exist for assignment '
                .$duplicate->tuition_assignment_id
                .' and reporter '
                .$duplicate->reported_by
                .'. Resolve duplicates before running this migration.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Add Guard Column
        |--------------------------------------------------------------------------
        */

        Schema::table(
            'complaints',
            function (Blueprint $table) {
                $table->unsignedTinyInteger(
                    'unresolved_guard'
                )
                    ->nullable()
                    ->after('status');
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Backfill Existing Unresolved Complaints
        |--------------------------------------------------------------------------
        */

        DB::table('complaints')
            ->whereIn(
                'status',
                [
                    'open',
                    'investigating',
                ]
            )
            ->update([
                'unresolved_guard' => 1,
            ]);

        /*
        |--------------------------------------------------------------------------
        | DB-Level Unique Guard
        |--------------------------------------------------------------------------
        |
        | MySQL allows multiple NULL values in a UNIQUE index.
        |
        | unresolved:
        |   same assignment + reporter + 1 => only one allowed
        |
        | resolved/rejected:
        |   guard = NULL => historical complaints can coexist
        |
        */

        Schema::table(
            'complaints',
            function (Blueprint $table) {
                $table->unique(
                    [
                        'tuition_assignment_id',
                        'reported_by',
                        'unresolved_guard',
                    ],
                    'complaints_unresolved_unique'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'complaints',
            function (Blueprint $table) {
                $table->dropUnique(
                    'complaints_unresolved_unique'
                );

                $table->dropColumn(
                    'unresolved_guard'
                );
            }
        );
    }
};