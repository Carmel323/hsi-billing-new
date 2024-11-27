<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clicks', function (Blueprint $table) {

            $table->bigIncrements('id')->unsigned()->change();

            $table->dropColumn('partners_affiliates_id');
        });
    }

    public function down()
    {
        Schema::table('clicks', function (Blueprint $table) {

            $table->unsignedBigInteger('partners_affiliates_id')->after('click_ts');

            $table->bigInteger('id')->unsigned(false)->change();
        });
    }
};
