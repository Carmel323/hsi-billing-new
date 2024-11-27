<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {   
        if (!Schema::hasTable('credit_notes')) { 

            Schema::create('credit_notes', function (Blueprint $table) {
                $table->id();
                $table->string('creditnote_id');
                $table->string('creditnote_number');
                $table->string('credited_date');
                $table->string('partner_name');
                $table->string('invoice_number');
                $table->string('partner_id');
                $table->string('status');
                $table->string('credited_amount');
                $table->string('balance');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
