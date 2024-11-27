<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {   
        if (!Schema::hasTable('plans')) {
         
            Schema::create('plans', function (Blueprint $table) {
                $table->id();
                $table->string('plan_name');
                $table->string('plan_id');
                $table->string('plan_code');
                $table->string('plan_description');
                $table->string('price');
                $table->string('quantity');
                $table->string('partner_id')->nullable();
                $table->timestamps();
            });
        }
    }

    
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
