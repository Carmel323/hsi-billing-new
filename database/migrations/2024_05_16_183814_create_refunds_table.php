<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {   
        if (!Schema::hasTable('refunds')) { 

            Schema::create('refunds', function (Blueprint $table) {
                $table->id();
                $table->string('date');
                $table->string('refund_id');
                $table->string('creditnote_id');
                $table->string('balance_amount');
                $table->string('refund_amount');
                $table->string('description');
                $table->string('partner_id');
                $table->string('creditnote_number');
                $table->timestamps();
            });
        }
    }
    
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
