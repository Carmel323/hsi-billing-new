<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class addPartnerNameInTermsTable extends Migration
{
    
    public function up(): void
    {
        if(!Schema::hasColumn('partner_name', 'terms')) {

            Schema::table('terms', function (Blueprint $table) {

                $table->string('partner_name')->nullable()->after('subscription_number');
            });
        }
    }

   
    public function down(): void
    {
        if (Schema::hasColumn('partner_name', 'terms')) {

            Schema::table('terms', function (Blueprint $table) {
                $table->dropColumn('partner_name');
            });
        }
    }
};
