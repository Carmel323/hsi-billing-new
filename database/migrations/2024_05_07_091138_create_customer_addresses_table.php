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
        if (!Schema::hasTable('customer_addresses')) { 
            
            Schema::create('customer_addresses', function (Blueprint $table) {
                $table->id();
                $table->string('full_name');
                $table->string('country');
                $table->string('address');
                $table->string('city');
                $table->string('state');
                $table->string('zip_code');
                $table->string('customer_id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
