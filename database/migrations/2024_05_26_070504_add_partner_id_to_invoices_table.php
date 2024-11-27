<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnerIdToInvoicesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('invoices', 'partner_id')) {
            
            Schema::table('invoices', function (Blueprint $table) {

                $table->unsignedBigInteger('partner_id')->nullable()->after('id');
               
            });
        }
    }

    public function down()
    {
        
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('partner_id');
        });
    }
}