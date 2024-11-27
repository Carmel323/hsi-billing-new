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
            if (!Schema::hasColumn('provider_data', 'uploaded_by')) {
                $table->string('uploaded_by')->after('zoho_cust_id');
            }
        });

        Schema::table('provider_availability_data', function (Blueprint $table) {
            if (!Schema::hasColumn('provider_availability_data', 'uploaded_by')) {
                $table->string('uploaded_by')->after('zoho_cust_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_data', function (Blueprint $table) {
            if (Schema::hasColumn('provider_data', 'uploaded_by')) {
                $table->dropColumn('uploaded_by');
            }
        });

        Schema::table('provider_availability_data', function (Blueprint $table) {
            if (Schema::hasColumn('provider_availability_data', 'uploaded_by')) {
                $table->dropColumn('uploaded_by');
            }
        });
    }
};
