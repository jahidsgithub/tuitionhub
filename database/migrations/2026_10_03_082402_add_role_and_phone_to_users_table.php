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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')
                ->nullable()
                ->unique()
                ->after('email');

            $table->enum('role', [
                'admin',
                'teacher',
                'student',
            ])
                ->default('student')
                ->after('phone');

            $table->enum('status', [
                'active',
                'inactive',
                'suspended',
            ])
                ->default('active')
                ->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'role',
                'status',
            ]);
        });
    }
};