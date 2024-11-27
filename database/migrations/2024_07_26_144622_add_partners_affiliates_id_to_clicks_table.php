<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnersAffiliatesIdToClicksTable extends Migration
{

    public function up()
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->bigInteger('partners_affiliates_id')->unsigned()->nullable()->after('click_ts');
        });
    }


    public function down()
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropColumn('partners_affiliates_id');
        });
    }
}
