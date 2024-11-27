<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClicksTable extends Migration
{

    public function up()
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('click_id');
            $table->string('click_source');
            $table->timestamp('click_ts');
            $table->string('partner_id');
            $table->string('advertiser_id');
            $table->string('zip');
            $table->string('state');
            $table->string('city');
            $table->string('channel')->nullable();
            $table->timestamps();

            $table->unique(['click_id', 'click_source'], 'click_id_click_source');
            $table->index(['click_ts', 'click_source']);
            $table->index('created_at');
            $table->index(['updated_at', 'created_at']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('clicks');
    }
}
