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
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'availability_data')) {
                $table->json('availability_data')->after('zip_code');
            }
            if (!Schema::hasColumn('leads', 'company_info')) {
                $table->json('company_info')->after('availability_data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'availability_data')) {
                $table->dropColumn('availability_data');
            }
            if (!Schema::hasColumn('leads', 'company_info')) {
                $table->dropColumn('company_info');
            }
        });
    }
};
