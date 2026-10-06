<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tuition_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tuition_post_id')
                ->constrained('tuition_posts')
                ->cascadeOnDelete();

            $table->foreignId('teacher_profile_id')
                ->constrained('teacher_profiles')
                ->cascadeOnDelete();

            $table->text('message')->nullable();

            $table->decimal('expected_salary', 10, 2)
                ->nullable();

            $table->enum('status', [
                'pending',
                'shortlisted',
                'accepted',
                'rejected',
                'withdrawn',
            ])->default('pending');

            $table->timestamps();

            $table->unique([
                'tuition_post_id',
                'teacher_profile_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tuition_applications');
    }
};