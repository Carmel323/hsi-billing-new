<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('otps_partner_user', function (Blueprint $table) {
            if (!Schema::hasColumn('otps_partner_user', 'lead_data')) {
                $table->json('lead_data')->nullable()->after('email');
            }
        });
    }

    public function down()
    {
        Schema::table('otps_partner_user', function (Blueprint $table) {
            if (Schema::hasColumn('otps_partner_user', 'lead_data')) {
                $table->dropColumn('lead_data');
            }
        });
    }
};
