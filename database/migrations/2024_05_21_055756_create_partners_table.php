<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {   
        if (!Schema::hasTable('partners')) { 
            
            Schema::create('partners', function (Blueprint $table) {
                $table->id();
                $table->string('partner_id');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('company_name');
                $table->string('email');
                $table->string('password')->nullable();
                $table->string('isp_partner_id')->nullable();
                $table->string('isp_advertiser_id')->nullable();
                $table->string('tax_number')->nullable();
                $table->string('invitation');
                $table->string('outstanding_invoices')->nullable();
                $table->string('unused_credits')->nullable();
                $table->string('partnerLastLoggedIn')->nullable();
                $table->timestamps();
            });
        }
    }

   
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
