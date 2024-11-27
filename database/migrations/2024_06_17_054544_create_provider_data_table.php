<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('provider_data', function (Blueprint $table) {
            $table->id();
            $table->string('template_csv');
            $table->string('logo_image');
            $table->string('landing_page_url');
            $table->string('landing_page_url_spanish')->nullable();
            $table->string('company_name')->nullable();
            $table->string('business_sales_phone_number')->nullable();
            $table->string('residential_sales_phone_number')->nullable();
            $table->string('zoho_cust_id');
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('provider_data');
    }
};
