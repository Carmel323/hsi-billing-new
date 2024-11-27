<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {   
        if (!Schema::hasTable('supports')) {   
                 
            Schema::create('supports', function (Blueprint $table) {
                $table->id();
                $table->string('date');
                $table->string('request_type');
                $table->string('subscription_number')->nullable();
                $table->string('message');
                $table->string('status');
                $table->string('partner_id')->nullable();
                $table->string('comments')->nullable();
                $table->json('attributes')->nullable();
                $table->timestamps();
            });
        }
    }

    
    public function down(): void
    {
        Schema::dropIfExists('supports');
    }
};
