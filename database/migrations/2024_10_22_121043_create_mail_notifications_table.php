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
        Schema::create('mail_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->boolean('partner_signup_mail')->default(0);
            $table->boolean('plan_purchase_mail')->default(0);
            $table->boolean('clicks_alert_mail')->default(0);
            $table->boolean('support_ticket_mail')->default(0);
            $table->boolean('data_submission_mail')->default(0);
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_notifications');
    }
};
