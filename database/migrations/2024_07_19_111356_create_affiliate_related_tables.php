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
        Schema::dropIfExists('isp_affiliate_ids');

        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->string('affiliate_id');
            $table->string('domain_name');
            $table->timestamps();
        });

        Schema::create('partners_affiliates', function (Blueprint $table) {
            $table->id();
            $table->string('partner_id');
            $table->string('affiliate_id');
            $table->timestamps();
        });
        Schema::table('clicks', function (Blueprint $table) {
            if (!Schema::hasColumn('clicks', 'affiliate_id')) {
                $table->string("affiliate_id")->after('partner_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('isp_affiliate_ids')) {

            Schema::create('isp_affiliate_ids', function (Blueprint $table) {
                $table->id();
                $table->string('isp_affiliate_id');
                $table->string('zoho_cust_id');
                $table->timestamps();
            });
        }

        Schema::dropIfExists('affiliates');

        Schema::dropIfExists('partners_affiliates');

        Schema::table('clicks', function (Blueprint $table) {
            if (Schema::hasColumn('clicks', 'affiliate_id')) {
                $table->dropColumn('affiliate_id');
            }
        });
    }
};
