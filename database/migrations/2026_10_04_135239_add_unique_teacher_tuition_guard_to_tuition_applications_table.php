<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Preflight Check
        |--------------------------------------------------------------------------
        |
        | Migration-এর আগে database-এ duplicate application আছে কি না দেখি।
        | Duplicate থাকলে unique index add না করে migration থামবে।
        |
        */

        $duplicate = DB::table('tuition_applications')
            ->select(
                'tuition_post_id',
                'teacher_profile_id',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(
                'tuition_post_id',
                'teacher_profile_id'
            )
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate) {
            throw new \RuntimeException(
                'Duplicate tuition applications exist for tuition_post_id '
                .$duplicate->tuition_post_id
                .' and teacher_profile_id '
                .$duplicate->teacher_profile_id
                .'. Resolve the duplicates before running this migration.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Database-Level Unique Guard
        |--------------------------------------------------------------------------
        */

        Schema::table(
            'tuition_applications',
            function (Blueprint $table) {
                $table->unique(
                    [
                        'tuition_post_id',
                        'teacher_profile_id',
                    ],
                    'tuition_application_teacher_unique'
                );
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(
            'tuition_applications',
            function (Blueprint $table) {
                $table->dropUnique(
                    'tuition_application_teacher_unique'
                );
            }
        );
    }
};