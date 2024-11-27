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
        Schema::table('provider_data', function (Blueprint $table) {
            if (!Schema::hasColumn('provider_data', 'tune_link')) {
                $table->string('tune_link')->after('zoho_cust_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_data', function (Blueprint $table) {
            if (Schema::hasColumn('provider_data', 'tune_link')) {
                $table->dropColumn('tune_link');
            }
        });
    }
};
