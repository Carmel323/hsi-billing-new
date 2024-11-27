<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class addPhoneNumberInPartnersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('phone_number', 'partners')) {

            Schema::table('partners', function (Blueprint $table) {

                $table->string('phone_number')->nullable()->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('phone_number', 'partners')) {

            Schema::table('partners', function (Blueprint $table) {
                $table->dropColumn('phone_number');
            });
        }
    }
};
