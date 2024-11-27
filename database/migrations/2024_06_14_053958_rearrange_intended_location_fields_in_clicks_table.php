<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RearrangeIntendedLocationFieldsInClicksTable extends Migration
{
    public function up()
    {
        Schema::table('clicks', function (Blueprint $table) {
            if (Schema::hasColumn('clicks', 'intended_city')) {
                $table->dropColumn('intended_city');
            }
            if (Schema::hasColumn('clicks', 'intended_state')) {
                $table->dropColumn('intended_state');
            }
            if (Schema::hasColumn('clicks', 'intended_zip')) {
                $table->dropColumn('intended_zip');
            }
        });

        Schema::table('clicks', function (Blueprint $table) {
            $table->string('intended_zip')->after('city');
            $table->string('intended_state')->after('intended_zip');
            $table->string('intended_city')->after('intended_state');
        });
    }

    public function down()
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropColumn('intended_city');
            $table->dropColumn('intended_state');
            $table->dropColumn('intended_zip');
        });

        Schema::table('clicks', function (Blueprint $table) {
            $table->string('intended_city');
            $table->string('intended_state');
            $table->string('intended_zip');
        });
    }
}
