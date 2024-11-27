<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyPartnerIdNullableInPlansTable extends Migration
{
    
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {

            if (Schema::hasColumn('plans', 'partner_id')) {
                $table->unsignedBigInteger('partner_id')->nullable()->change();
            }
        });
    }

   
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {

            if (Schema::hasColumn('plans', 'partner_id')) {
                $table->unsignedBigInteger('partner_id')->nullable(false)->change();
            }
        });
    }
}
