<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class addCompanyNameInSubscriptionTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('company_name', 'subscriptions')) {

            Schema::table('subscriptions', function (Blueprint $table) {

                $table->string('company_name')->nullable()->after('partner_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('company_name', 'subscriptions')) {

            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn('company_name');
            });
        }
    }
};
