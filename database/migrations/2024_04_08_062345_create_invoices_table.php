<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {   
         if (!Schema::hasTable('invoices')) {

            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_id');
                $table->string('invoice_date');
                $table->string('invoice_number');
                $table->string('plan_id');
                $table->string('plan_name');
                $table->string('price');
                $table->string('credits_applied');
                $table->string('discount');
                $table->string('payment_made');
                $table->string('invoice_link');
                $table->string('partner_id');
                $table->timestamps();
            });
        }
    }


    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
