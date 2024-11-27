<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateClicksTable extends Migration
{
    
    public function up()
    {
        Schema::dropIfExists('clicks');

        Schema::create('clicks', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->string('click_id', 255)->nullable(false);

            $table->string('click_source', 255)->nullable(false);

            $table->timestamp('click_ts')->nullable(false);

            $table->bigInteger('partner_id')->nullable(false); 

            $table->string('zip', 255)->nullable(false);

            $table->string('state', 255)->nullable(false);

            $table->string('city', 255)->nullable(false);

            $table->string('channel', 255)->nullable()->default(null);

            $table->timestamp('created_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->timestamp('updated_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            $table->unique(['click_id', 'click_source'], 'click_id_click_source');

            $table->index(['click_ts', 'click_source'], 'clicks_click_ts_click_source_index');
        });
    }

   
    public function down()
    {
        Schema::dropIfExists('clicks');
    }
}
