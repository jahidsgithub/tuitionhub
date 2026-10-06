<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tuition_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tuition_post_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('teacher_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('student_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('source', [
                'application',
                'direct_request',
            ]);

            $table->foreignId('tuition_application_id')
                ->nullable()
                ->constrained('tuition_applications')
                ->nullOnDelete();

            $table->foreignId('teacher_request_id')
                ->nullable()
                ->constrained('teacher_requests')
                ->nullOnDelete();

            $table->enum('status', [
                'active',
                'completed',
                'cancelled',
            ])->default('active');

            $table->timestamp('assigned_at');

            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamp('cancelled_at')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | One Tuition = One Final Assignment
            |--------------------------------------------------------------------------
            */

            $table->unique(
                'tuition_post_id'
            );

            $table->index([
                'teacher_profile_id',
                'status',
            ]);

            $table->index([
                'student_user_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'tuition_assignments'
        );
    }
};