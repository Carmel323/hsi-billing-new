<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePartnerUsersTable extends Migration
{
    
    public function up()
    {
        Schema::table('partner_users', function (Blueprint $table) {
            $table->renameColumn('user_id', 'zoho_cpid');
            $table->boolean('is_primary')->default(false);
        });
    }

    
    public function down()
    {
        Schema::table('partner_users', function (Blueprint $table) {
            $table->renameColumn('zoho_cpid', 'user_id');
            $table->dropColumn('is_primary');
        });
    }
}
