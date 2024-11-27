<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCustomersAndCustomerAddressesTables extends Migration
{
   
    public function up()
    {
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }

    
    public function down()
    {
        // No need to recreate the tables in the down method
    }
}
