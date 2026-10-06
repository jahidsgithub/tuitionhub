<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tuition_assignment_id')
                ->nullable()
                ->constrained('tuition_assignments')
                ->nullOnDelete();

            $table->foreignId('reported_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('reported_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('category', [
                'behavior',
                'payment',
                'attendance',
                'misinformation',
                'harassment',
                'safety',
                'other',
            ]);

            $table->string('subject');

            $table->text('description');

            $table->enum('status', [
                'open',
                'investigating',
                'resolved',
                'rejected',
            ])->default('open');

            $table->text('admin_note')
                ->nullable();

            $table->foreignId('handled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('resolved_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'status',
                'category',
            ]);

            $table->index([
                'reported_by',
                'status',
            ]);

            $table->index([
                'reported_user_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};