<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClicksEmailLogTable extends Migration
{
    public function up()
    {
        Schema::create('clicks_email_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners');
            $table->json('details');
            $table->timestamp('timestamp');
            $table->integer('clicks_month');
            $table->integer('clicks_year');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clicks_email_log');
    }
}
