<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {

        if (!Schema::hasColumn('invoices', 'partner_details')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->json('payment_details')->nullable()->after('zoho_cust_id');
            });
        }
    }

   
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('payment_details');
        });
    }
};
