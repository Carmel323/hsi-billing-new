<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderAvailabilityDataTable extends Migration
{
    
    public function up()
    {
        Schema::create('provider_availability_data', function (Blueprint $table) {
            $table->id();
            $table->string('zoho_cust_id'); 
            $table->string('file_name'); 
            $table->bigInteger('file_size'); 
            $table->integer('zip_count'); 
            $table->string('url');
            $table->timestamps();
        });
    }

    
    public function down()
    {
        Schema::dropIfExists('provider_availability_data');
    }
}
