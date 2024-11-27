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
        Schema::dropIfExists('password_tokens_partner');

        Schema::dropIfExists('otps_partner');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('password_tokens_partner', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('password_token');
            $table->timestamps();
        });

        Schema::create('otps_partner', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('otp');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('expires_at')->nullable(); // Make expires_at column nullable
        });
    }
};