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
            if (!Schema::hasColumn('partners', 'selected_plans')) {
                $table->json('selected_plans')->nullable()->after('tax_number');
            }
            if (!Schema::hasColumn('partners', 'is_approved')) {
                $table->boolean('is_approved')->default(0)->after('selected_plans');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (Schema::hasColumn('partners', 'selected_plans')) {
                $table->dropColumn('selected_plans');
            }
            if (Schema::hasColumn('partners', 'is_approved')) {
                $table->dropColumn('is_approved');
            }
        });
    }
};
