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
        Schema::table('clicks', function (Blueprint $table) {

            $table->string('intended_city')->after('channel');
            $table->string('intended_state')->after('intended_city');
            $table->string('intended_zip')->after('intended_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropColumn('intended_city');
            $table->dropColumn('intended_state');
            $table->dropColumn('intended_zip');
        });
    }
};
