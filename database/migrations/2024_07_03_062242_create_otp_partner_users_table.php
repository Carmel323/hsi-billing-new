<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('otps_partner_user', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('otp');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('expires_at')->nullable(); // Make expires_at column nullable
        });
    }

    public function down()
    {
        Schema::dropIfExists('otps_partner_user');
    }
};
