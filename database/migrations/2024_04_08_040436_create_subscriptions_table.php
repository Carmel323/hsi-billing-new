<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


    return new class extends Migration
    {
        public function up(): void
        {
            if (!Schema::hasTable('subscriptions')) {
                Schema::create('subscriptions', function (Blueprint $table) {
                    $table->id();
                    $table->string('subscription_id');
                    $table->string('subscription_number');
                    $table->string('plan_name');
                    $table->string('plan_price');
                    $table->string('status');
                    $table->string('start_date');
                    $table->string('next_billing_at');
                    $table->string('plan_id');
                    $table->string('partner_id');
                    $table->string('partner_name');
                    $table->string('invoice_id');
                    $table->string('card_id');
                    $table->string('addon')->nullable();
                    $table->timestamps();
                });
            }
        }

        public function down(): void
        {
            Schema::dropIfExists('subscriptions');
        }
    };

