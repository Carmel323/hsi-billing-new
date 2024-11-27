<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameIspPartnerIdColumn extends Migration
{
    
    public function up()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->renameColumn('isp_partner_id', 'isp_affiliate_id');
        });
    }

    
    public function down()
    {
        Schema::table('partners', function (Blueprint $table) {

            $table->renameColumn('isp_affiliate_id', 'isp_partner_id');
            
        });
    }
}
