<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePartnersTable extends Migration
{
    public function up()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned()->change();
            $table->unique('isp_advertiser_id');
        });
    }

    public function down()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropUnique(['isp_advertiser_id']);
            $table->bigInteger('id')->unsigned(false)->change();
        });
    }
}
