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
        Schema::rename('otps', 'otps_partner');
        Schema::rename('password_tokens', 'password_tokens_partner');
        Schema::rename('users', 'partner_users');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('otps_partner', 'otps');
        Schema::rename('password_tokens_partner', 'password_tokens');
        Schema::rename('partner_users', 'users');
    }
};
