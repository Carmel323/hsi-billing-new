<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {   
        if (!Schema::hasTable('cards')) {

            Schema::create('cards', function (Blueprint $table) {
                $table->id();
                $table->string('card_id');
                $table->string('last_four_digits');
                $table->string('payment_gateway');
                $table->string('expiry_month');
                $table->string('expiry_year');
                $table->string('partner_id');
                $table->timestamps();
            });
        }
    }

    
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
