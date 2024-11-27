<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {   
        if (!Schema::hasTable('customers')) {
            
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('customer_id');
                $table->string('partner_name');
                $table->string('company_name');
                $table->string('email');
                $table->string('password')->nullable();
                $table->string('partner_id')->nullable();
                $table->string('advertiser_id')->nullable();
                $table->string('invitation');
                $table->string('customerLastLoggedIn')->nullable();
                $table->timestamps();
            });
        }
    }

    
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
