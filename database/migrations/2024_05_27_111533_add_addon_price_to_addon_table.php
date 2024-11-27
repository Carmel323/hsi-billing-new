<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddonPriceToAddonTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('add_ons', 'addon_price')) {
            Schema::table('add_ons', function (Blueprint $table) {
                $table->string('addon_price')->nullable()->after('addon_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('add_ons', function (Blueprint $table) {
            $table->dropColumn('addon_price');
        });
    }
};
