<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnerIdToPlansTable extends Migration
{
   
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'partner_id')) {
                $table->bigInteger('partner_id')->unsigned()->after('id');
            }
        });
    }

    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'partner_id')) {
                $table->dropColumn('partner_id');
            }
        });
    }
}
