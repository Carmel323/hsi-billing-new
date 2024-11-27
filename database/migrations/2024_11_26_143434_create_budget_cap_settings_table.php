<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budget_cap_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->references('id')->on('partners');
            $table->boolean('clicks_pace_toggle')->default(0);
            $table->boolean('invoice_pace_toggle')->default(0);
            $table->boolean('budget_cap_toggle')->default(0);
            $table->string('plan_type');
            $table->string('click_limit')->nullable();
            $table->string('cost_limit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_cap_settings');
    }
};
