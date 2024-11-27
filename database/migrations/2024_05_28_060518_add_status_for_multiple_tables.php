<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusForMultipleTables extends Migration
{
    public function up(): void
    {
        $tables = [
            'cards',
            'invoices',
            'partners',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'status')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('status')->default('active');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'cards',
            'invoices',
            'partners',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'status')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('status');
                });
            }
        }
    }
};
