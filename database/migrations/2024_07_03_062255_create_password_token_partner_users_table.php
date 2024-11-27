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
        if (!Schema::hasTable('password_tokens_partner_user')) {

            Schema::create('password_tokens_partner_user', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('password_token');
                $table->timestamps();
            });
        }
    }


    public function down(): void
    {
        Schema::dropIfExists('password_tokens_partner_user');
    }
};
