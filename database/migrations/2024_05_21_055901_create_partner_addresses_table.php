<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {   
        if (!Schema::hasTable('partner_addresses')) { 

            Schema::create('partner_addresses', function (Blueprint $table) {
                $table->id();
                $table->string('full_name');
                $table->string('country');
                $table->string('street');
                $table->string('city');
                $table->string('state');
                $table->string('zip_code');
                $table->string('partner_id');
                $table->timestamps();
            });
        }
    }

    
    public function down(): void
    {
        Schema::dropIfExists('partner_addresses');
    }
};
