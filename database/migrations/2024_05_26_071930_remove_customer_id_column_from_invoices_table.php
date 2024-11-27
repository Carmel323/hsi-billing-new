<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCustomerIdColumnFromInvoicesTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('invoices', 'customer_id')) {
            
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('customer_id');
            });
        }
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {

            $table->unsignedBigInteger('customer_id')->nullable();
        });
    }
}
