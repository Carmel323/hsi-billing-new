<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTemplateCsvFromProviderDataTable extends Migration
{
    
    public function up()
    {
        Schema::table('provider_data', function (Blueprint $table) {
            $table->dropColumn('template_csv');
        });
    }

    
    public function down()
    {
        Schema::table('provider_data', function (Blueprint $table) {
            $table->string('template_csv')->nullable();
        });
    }
}
