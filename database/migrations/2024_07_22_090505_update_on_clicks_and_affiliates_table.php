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
        Schema::table('affiliates', function (Blueprint $table) {
            $table->renameColumn('affiliate_id', 'isp_affiliate_id');
        });

        Schema::table('clicks', function (Blueprint $table) {
            if (Schema::hasColumn('clicks', 'affiliate_id')) {
                $table->dropColumn('affiliate_id');
            }
            if (Schema::hasColumn('clicks', 'partner_id')) {
                $table->dropColumn('partner_id');
            }
            if (!Schema::hasColumn('clicks', 'partners_affiliates_id')) {
                $table->string("partners_affiliates_id")->after('click_ts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {


        Schema::table('clicks', function (Blueprint $table) {
            if (!Schema::hasColumn('clicks', 'partner_id')) {
                $table->string("partner_id")->after('click_ts');
            }
            if (!Schema::hasColumn('clicks', 'affiliate_id')) {
                $table->string("affiliate_id")->after('partner_id');
            }
            if (Schema::hasColumn('clicks', 'partners_affiliates_id')) {
                $table->dropColumn('partners_affiliates_id');
            }
        });

        Schema::table('affiliates', function (Blueprint $table) {
            $table->renameColumn('isp_affiliate_id', 'affiliate_id');
        });
    }
};
