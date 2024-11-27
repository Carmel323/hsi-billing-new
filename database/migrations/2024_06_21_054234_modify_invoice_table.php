<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'plan_id')) {
                $table->dropColumn('plan_id');
            }

            if (Schema::hasColumn('invoices', 'addon_code')) {
                $table->dropColumn('addon_code');
            }

            if (!Schema::hasColumn('invoices', 'invoice_items')) {
                $table->json('invoice_items')->after('invoice_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'plan_id')) {
                $table->string('plan_id');
            }

            if (!Schema::hasColumn('invoices', 'addon_code')) {
                $table->string('addon_code');
            }

            if (Schema::hasColumn('invoices', 'invoice_items')) {
                $table->dropColumn('invoice_items');
            }
        });
    }
};
