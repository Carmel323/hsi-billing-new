<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentMethodsTableAndDropCardsTable extends Migration
{
    
    public function up()
    {
        Schema::dropIfExists('cards');

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->renameColumn('card_id', 'payment_method_id');
        });
        

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('partner_id');
            $table->string('payment_method_id');
            $table->string('type');
            $table->string('last_four_digits')->nullable();
            $table->string('expiry_year');
            $table->string('expiry_month');
            $table->string('payment_gateway');
            $table->string('status')->default('active');
            $table->timestamps();
        
        });
    }

  
    public function down()
    {
        Schema::dropIfExists('payment_methods');

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->renameColumn('payment_method_id', 'card_id');
        });

        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_id');
            $table->string('last_four_digits');
            $table->string('payment_gateway');
            $table->string('expiry_month');
            $table->string('expiry_year');
            $table->string('partner_id');
            $table->timestamps();
            $table->string('status')->default('active');
        });
    }
}
