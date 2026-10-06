<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action');

            $table->string('route_name')
                ->nullable();

            $table->string('method', 10);

            $table->string('target_type')
                ->nullable();

            $table->string('target_id')
                ->nullable();

            $table->json('metadata')
                ->nullable();

            $table->timestamp('created_at')
                ->useCurrent();

            $table->index([
                'actor_user_id',
                'created_at',
            ]);

            $table->index([
                'action',
                'created_at',
            ]);

            $table->index([
                'target_type',
                'target_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};