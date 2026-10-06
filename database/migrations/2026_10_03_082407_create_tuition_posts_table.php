<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tuition_posts', function (Blueprint $table) {
            $table->id();

            $table->string('tuition_code')->unique();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('location_id')
                ->nullable()
                ->constrained('locations')
                ->nullOnDelete();

            $table->string('title');

            $table->string('class_level');

            $table->enum('medium', [
                'bangla',
                'english',
                'english_version',
                'madrasa',
                'other',
            ])->nullable();

            $table->enum('student_gender', [
                'male',
                'female',
                'other',
            ])->nullable();

            $table->enum('preferred_teacher_gender', [
                'male',
                'female',
                'any',
            ])->default('any');

            $table->unsignedTinyInteger('days_per_week')->nullable();

            $table->decimal('salary', 10, 2)->nullable();

            $table->enum('teaching_mode', [
                'offline',
                'online',
                'both',
            ])->default('offline');

            $table->text('requirements')->nullable();

            $table->enum('status', [
                'draft',
                'pending',
                'published',
                'filled',
                'closed',
            ])->default('pending');

            $table->timestamp('published_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tuition_posts');
    }
};