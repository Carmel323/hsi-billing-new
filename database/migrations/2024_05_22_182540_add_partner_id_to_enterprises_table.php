<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnerIdToEnterprisesTable extends Migration
{
    
    public function up()
    {
        Schema::table('enterprises', function (Blueprint $table) {

            if (!Schema::hasColumn('enterprises', 'partner_id')) {

                $table->unsignedBigInteger('partner_id')->nullable()->after('id'); 
            }
        });
    }

    
    public function down()
    {
        Schema::table('enterprises', function (Blueprint $table) {

            if (Schema::hasColumn('enterprises', 'partner_id')) {
                
                $table->dropColumn('partner_id');
            }
        });
    }
}
