<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnerIdToSubscriptionsTable extends Migration
{
    
    public function up()
    {
        
        if (!Schema::hasColumn('subscriptions', 'partner_id')) {

            Schema::table('subscriptions', function (Blueprint $table) {
                
                $table->unsignedBigInteger('partner_id')->nullable()->after('plan_id');
            });
        }
    }

   
    public function down()
    {
        if (Schema::hasColumn('subscriptions', 'partner_id')) {

            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn('partner_id');
            });
        }
    }
}
