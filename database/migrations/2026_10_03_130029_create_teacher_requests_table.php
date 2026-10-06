<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('teacher_profile_id')
                ->constrained('teacher_profiles')
                ->cascadeOnDelete();

            $table->foreignId('tuition_post_id')
                ->nullable()
                ->constrained('tuition_posts')
                ->nullOnDelete();

            $table->text('message')->nullable();

            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'cancelled',
            ])->default('pending');

            $table->timestamps();

            $table->index([
                'student_user_id',
                'status',
            ]);

            $table->index([
                'teacher_profile_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_requests');
    }
};