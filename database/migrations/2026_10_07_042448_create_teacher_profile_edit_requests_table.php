<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'teacher_profile_edit_requests',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->foreignId(
                        'teacher_profile_id'
                    )
                    ->constrained(
                        'teacher_profiles'
                    )
                    ->cascadeOnDelete();

                $table
                    ->foreignId(
                        'requested_by_user_id'
                    )
                    ->constrained(
                        'users'
                    )
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Requested Profile Data
                |--------------------------------------------------------------------------
                |
                | Teacher-এর requested changes JSON হিসেবে save হবে।
                |
                */

                $table->json(
                    'requested_data'
                );

                /*
                |--------------------------------------------------------------------------
                | Requested New Photo
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'profile_photo_path'
                    )
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Request Status
                |--------------------------------------------------------------------------
                |
                | pending
                | approved
                | rejected
                |
                */

                $table
                    ->string(
                        'status',
                        20
                    )
                    ->default(
                        'pending'
                    )
                    ->index();

                /*
                |--------------------------------------------------------------------------
                | One Pending Request Per Teacher
                |--------------------------------------------------------------------------
                |
                | pending থাকা অবস্থায় unique key থাকবে।
                |
                | approve/reject হওয়ার পরে active_key = NULL হবে।
                |
                | এর ফলে একই teacher একই সময়ে একাধিক pending request
                | submit করতে পারবে না।
                |
                */

                $table
                    ->string(
                        'active_key',
                        191
                    )
                    ->nullable()
                    ->unique();

                /*
                |--------------------------------------------------------------------------
                | Admin Review
                |--------------------------------------------------------------------------
                */

                $table
                    ->text(
                        'admin_note'
                    )
                    ->nullable();

                $table
                    ->foreignId(
                        'reviewed_by_user_id'
                    )
                    ->nullable()
                    ->constrained(
                        'users'
                    )
                    ->nullOnDelete();

                $table
                    ->timestamp(
                        'reviewed_at'
                    )
                    ->nullable();

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | Index
                |--------------------------------------------------------------------------
                */

                $table->index([
                    'teacher_profile_id',
                    'status',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'teacher_profile_edit_requests'
        );
    }
};