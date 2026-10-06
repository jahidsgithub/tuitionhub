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
        */

        $duplicate = DB::table('teacher_reviews')
            ->select(
                'tuition_assignment_id',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(
                'tuition_assignment_id'
            )
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate) {
            throw new \RuntimeException(
                'Multiple teacher reviews already exist for tuition_assignment_id '
                .$duplicate->tuition_assignment_id
                .'. Resolve the duplicates before running this migration.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | One Review Per Tuition Assignment
        |--------------------------------------------------------------------------
        */

        Schema::table(
            'teacher_reviews',
            function (Blueprint $table) {
                $table->unique(
                    'tuition_assignment_id',
                    'teacher_reviews_assignment_unique'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'teacher_reviews',
            function (Blueprint $table) {
                $table->dropUnique(
                    'teacher_reviews_assignment_unique'
                );
            }
        );
    }
};