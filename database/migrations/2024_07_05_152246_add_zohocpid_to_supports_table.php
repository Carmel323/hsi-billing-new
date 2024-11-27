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
        Schema::table('supports', function (Blueprint $table) {
            if (!Schema::hasColumn('supports', 'zoho_cpid')) {
                $table->string('zoho_cpid')->after('zoho_cust_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supports', function (Blueprint $table) {
            if (Schema::hasColumn('supports', 'zoho_cpid')) {
                $table->dropColumn('zoho_cpid');
            }
        });
    }
};
