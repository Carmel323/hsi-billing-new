<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnerIdToMultipleTables extends Migration
{
    public function up()
    {
        $tables = [
            'cards',
            'credit_notes',
            'customers',
            'enterprises',
            'invoices',
            'partner_addresses',
            'partners',
            'plans',
            'refunds',
            'subscriptions',
            'supports',
            'terms'
        ];

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'partner_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->unsignedBigInteger('partner_id')->nullable()->after('id');
                });
            }
        }
    }

    public function down()
    {
        $tables = [
            'cards',
            'credit_notes',
            'customers',
            'enterprises',
            'invoices',
            'partner_addresses',
            'partners',
            'plans',
            'refunds',
            'subscriptions',
            'supports',
            'terms'
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'partner_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('partner_id');
                });
            }
        }
    }
}
