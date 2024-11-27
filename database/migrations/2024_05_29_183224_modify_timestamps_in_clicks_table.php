<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyTimestampsInClicksTable extends Migration
{
    public function up()
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropTimestamps();
        });

        Schema::table('clicks', function (Blueprint $table) {

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable(false);

            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable(false)->onUpdate(DB::raw('CURRENT_TIMESTAMP'));
        });
    }


    public function down()
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });

        Schema::table('clicks', function (Blueprint $table) {
            $table->timestamps();
        });
    }
}
