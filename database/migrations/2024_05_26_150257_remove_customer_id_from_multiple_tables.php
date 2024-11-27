<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCustomerIdFromMultipleTables extends Migration
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
            if (Schema::hasColumn($table, 'customer_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('customer_id');
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

            Schema::table($table, function (Blueprint $table) {

                $table->unsignedBigInteger('customer_id')->nullable();
            });
        }
    }
}
