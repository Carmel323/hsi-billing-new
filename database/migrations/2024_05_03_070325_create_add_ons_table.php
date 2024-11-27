<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        if (!Schema::hasTable('add_ons')) {

            Schema::create('add_ons', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('addon_code')->nullable();
                $table->string('plan_id');
                $table->timestamps();
            });
        }
    }


    public function down(): void
    {
        Schema::dropIfExists('add_ons');
    }
};
