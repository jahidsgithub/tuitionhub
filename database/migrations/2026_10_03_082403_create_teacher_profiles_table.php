<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('profile_photo')->nullable();

            $table->enum('gender', [
                'male',
                'female',
                'other',
            ])->nullable();

            $table->string('university')->nullable();
            $table->string('department')->nullable();
            $table->string('degree')->nullable();

            $table->unsignedInteger('experience_years')
                ->default(0);

            $table->text('bio')->nullable();

            $table->decimal('expected_salary_min', 10, 2)
                ->nullable();

            $table->decimal('expected_salary_max', 10, 2)
                ->nullable();

            $table->enum('teaching_mode', [
                'offline',
                'online',
                'both',
            ])->default('offline');

            $table->boolean('is_verified')
                ->default(false);

            $table->boolean('is_available')
                ->default(true);

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};