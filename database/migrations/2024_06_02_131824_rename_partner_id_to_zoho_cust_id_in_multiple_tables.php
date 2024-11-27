<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePartnerIdToZohoCustIdInMultipleTables extends Migration
{
    
    public function up()
    {
        $tables = [
            'credit_notes',
            'enterprises',
            'invoices',
            'partner_addresses',
            'partners',
            'payment_methods',
            'refunds',
            'subscriptions',
            'supports',
            'terms',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->renameColumn('partner_id', 'zoho_cust_id');
            });
        }
    }

    
    public function down()
    {
        $tables = [
            'credit_notes',
            'enterprises',
            'invoices',
            'partner_addresses',
            'partners',
            'payment_methods',
            'refunds',
            'subscriptions',
            'supports',
            'terms',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->renameColumn('zoho_cust_id', 'partner_id');
            });
        }
    }
}
