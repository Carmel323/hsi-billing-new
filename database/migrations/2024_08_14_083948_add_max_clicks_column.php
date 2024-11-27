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
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'max_clicks')) {
                $table->string('max_clicks')->after('quantity');
            }
        });

        Schema::table('add_ons', function (Blueprint $table) {
            if (!Schema::hasColumn('add_ons', 'max_clicks')) {
                $table->string('max_clicks')->nullable()->after('addon_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'max_clicks')) {
                $table->dropColumn('max_clicks');
            }
        });

        Schema::table('add_ons', function (Blueprint $table) {
            if (Schema::hasColumn('add_ons', 'max_clicks')) {
                $table->dropColumn('max_clicks');
            }
        });
    }
};
