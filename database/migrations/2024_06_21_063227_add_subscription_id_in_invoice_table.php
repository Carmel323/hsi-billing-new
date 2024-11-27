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
            if (!Schema::hasColumn('invoices', 'subscription_id')) {
                $table->json('subscription_id')->after('invoice_items');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {


        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'subscription_id')) {
                $table->dropColumn('subscription_id');
            }
        });
    }
};
