<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePartnersAffiliatesTable extends Migration
{
    public function up()
    {
        Schema::table('partners_affiliates', function (Blueprint $table) {

            $table->bigIncrements('id')->unsigned()->change();
            $table->unsignedBigInteger('partner_id')->change();
            $table->unsignedBigInteger('affiliate_id')->change();
            $table->unique(['partner_id', 'affiliate_id'], 'partner_id_affiliate_id');
        });
    }

    public function down()
    {
        Schema::table('partners_affiliates', function (Blueprint $table) {
            $table->dropUnique('partner_id_affiliate_id');
        });
    }
}
