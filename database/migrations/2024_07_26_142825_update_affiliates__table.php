<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAffiliatesTable extends Migration
{
    public function up()
    {
        Schema::table('affiliates', function (Blueprint $table) {

            $table->bigIncrements('id')->unsigned()->change();

            $table->unique('isp_affiliate_id')->change();
        });
    }

    public function down()
    {
        Schema::table('affiliates', function (Blueprint $table) {

            $table->dropUnique(['isp_affiliate_id']);
        });
    }
}
