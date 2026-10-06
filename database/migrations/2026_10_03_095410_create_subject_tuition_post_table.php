<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_tuition_post', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->foreignId('tuition_post_id')
                ->constrained('tuition_posts')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'subject_id',
                'tuition_post_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_tuition_post');
    }
};