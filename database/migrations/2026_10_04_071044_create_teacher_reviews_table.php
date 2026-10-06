<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tuition_assignment_id')
                ->constrained('tuition_assignments')
                ->cascadeOnDelete();

            $table->foreignId('teacher_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('student_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('rating');

            $table->text('review')
                ->nullable();

            $table->timestamps();

            $table->unique(
                'tuition_assignment_id'
            );

            $table->index([
                'teacher_profile_id',
                'rating',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'teacher_reviews'
        );
    }
};