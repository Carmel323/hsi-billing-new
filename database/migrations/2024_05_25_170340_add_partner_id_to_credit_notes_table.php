<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnerIdToCreditNotesTable extends Migration
{
    
    public function up()
    {
        if (!Schema::hasColumn('credit_notes', 'partner_id')) {
            Schema::table('credit_notes', function (Blueprint $table) {
                 $table->bigInteger('partner_id')->unsigned()->after('id');
            });
        }
    }

    
    public function down()
    {
        
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn('partner_id');
        });
    }
}
