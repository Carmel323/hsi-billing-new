<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnsFromPartnersTable extends Migration
{
    
    public function up()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'email', 'phone_number', 'password', 'invitation', 'partnerLastLoggedIn']);
        });
    }

    
    public function down()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->string('password');
            $table->string('invitation')->nullable();
            $table->timestamp('partnerLastLoggedIn')->nullable();
        });
    }
}
