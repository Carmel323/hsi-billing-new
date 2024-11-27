<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class  DropPartnerIdFromPlansTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('plans', 'partner_id')) {

            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('partner_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {

            $table->unsignedBigInteger('partner_id')->nullable();
        });
    }
};
