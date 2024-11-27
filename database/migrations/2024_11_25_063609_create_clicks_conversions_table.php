<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateClicksConversionsTable extends Migration
{
    public function up()
    {
        Schema::create('clicks_conversions', function (Blueprint $table) {
            $table->id();
            $table->string('conversion_id');
            $table->string('conversion_source');
            $table->bigInteger('click_id')->unsigned();
            $table->foreign('click_id')->references('id')->on('clicks')->onDelete('cascade');
            $table->unique(['conversion_id', 'conversion_source'], 'unique_conversion_source');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->notNull();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->notNull();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clicks_conversions');
    }
}
