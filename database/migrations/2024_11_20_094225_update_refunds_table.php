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
        Schema::table('refunds', function (Blueprint $table) {

            if (!Schema::hasColumn('refunds', 'gateway_transaction_id')) {
                $table->string('gateway_transaction_id')->after('status');
            }
            if (!Schema::hasColumn('refunds', 'refund_mode')) {
                $table->string('refund_mode')->after('gateway_transaction_id');
            }
            if (!Schema::hasColumn('refunds', 'payment_method_id')) {
                $table->string('payment_method_id')->after('refund_mode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {

            if (Schema::hasColumn('refunds', 'gateway_transaction_id')) {
                $table->dropColumn('gateway_transaction_id');
            }
            if (Schema::hasColumn('refunds', 'refund_mode')) {
                $table->dropColumn('refund_mode');
            }
            if (Schema::hasColumn('refunds', 'payment_method_id')) {
                $table->dropColumn('payment_method_id');
            }
        });
    }
};
