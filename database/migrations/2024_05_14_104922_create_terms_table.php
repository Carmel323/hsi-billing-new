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
        if (!Schema::hasTable('terms')) { 
            Schema::create('terms', function (Blueprint $table) {
                $table->id();
                $table->string('partner_id');
                $table->string('subscription_number');
                $table->string('ip_address');
                $table->string('browser_agent');
                $table->boolean("consent")->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
