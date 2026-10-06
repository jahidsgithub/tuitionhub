<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();

            $table->string('bkash_number')->nullable();
            $table->string('bkash_account_type')->nullable();

            $table->string('nagad_number')->nullable();
            $table->string('nagad_account_type')->nullable();

            $table->boolean('bkash_enabled')->default(false);
            $table->boolean('nagad_enabled')->default(false);

            $table->text('payment_instruction')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};