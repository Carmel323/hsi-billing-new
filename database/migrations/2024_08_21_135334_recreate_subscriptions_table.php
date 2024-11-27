<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('subscriptions');

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('zoho_cust_id');
            $table->string('subscription_id')->unique(); 
            $table->string('subscription_number');
            $table->string('start_date');
            $table->string('next_billing_at');
            $table->string('plan_id');
            $table->string('invoice_id');
            $table->string('payment_method_id');
            $table->string('addon')->nullable();
            $table->string('isCustom')->nullable();
            $table->string('status');
            $table->timestamps(); 
        });
    }

    
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
