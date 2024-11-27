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
        Schema::table('partners', function (Blueprint $table) {
            if (!Schema::hasColumn('partners', 'is_approved')) {
                $table->boolean("is_approved")->default(0)->after('unused_credits');
            }
            if (!Schema::hasColumn('partners', 'lead_source')) {
                $table->string("lead_source")->after('is_approved');
            }
            if (Schema::hasColumn('partners', 'isp_affiliate_id')) {
                $table->dropColumn('isp_affiliate_id');
            }
        });
        if (!Schema::hasTable('isp_affiliate_ids')) {

            Schema::create('isp_affiliate_ids', function (Blueprint $table) {
                $table->id();
                $table->string('isp_affiliate_id');
                $table->string('zoho_cust_id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (Schema::hasColumn('partners', 'is_approved')) {
                $table->dropColumn('is_approved');
            }
            if (Schema::hasColumn('partners', 'lead_source')) {
                $table->dropColumn('lead_source');
            }
            if (!Schema::hasColumn('partners', 'isp_affiliate_id')) {
                $table->string("isp_affiliate_id")->after('company_name');
            }
        });

        Schema::dropIfExists('isp_affiliate_ids');
    }
};
