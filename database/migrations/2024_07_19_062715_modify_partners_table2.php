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
        Schema::table('partners', function (Blueprint $table) {
            if (Schema::hasColumn('partners', 'is_approved')) {
                $table->dropColumn('is_approved');
            }
            if (Schema::hasColumn('partners', 'lead_source')) {
                $table->dropColumn('lead_source');
            }
        });
        if (!Schema::hasTable('leads')) {

            Schema::create('leads', function (Blueprint $table) {
                $table->id();
                $table->string('company_name');
                $table->string('tax_number');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email');
                $table->string('phone_number');
                $table->string('street');
                $table->string('city');
                $table->string('state');
                $table->string('zip_code');
                $table->string("status")->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (!Schema::hasColumn('partners', 'is_approved')) {
                $table->boolean("is_approved")->default(0)->after('unused_credits');
            }
            if (!Schema::hasColumn('partners', 'lead_source')) {
                $table->string("lead_source")->after('is_approved');
            }
        });

        Schema::dropIfExists('leads');
    }
};
