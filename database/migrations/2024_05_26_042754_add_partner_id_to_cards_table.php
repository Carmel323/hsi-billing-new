<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnerIdToCardsTable extends Migration
{
    
    public function up()
    {
        Schema::table('cards', function (Blueprint $table) {
            if (!Schema::hasColumn('cards', 'partner_id')) {
                $table->unsignedBigInteger('partner_id')->nullable()->after('id'); 
            }
        });
    }

   
    public function down()
    {
        Schema::table('cards', function (Blueprint $table) {
            if (Schema::hasColumn('cards', 'partner_id')) {
                $table->dropColumn('partner_id');
            }
        });
    }
}
