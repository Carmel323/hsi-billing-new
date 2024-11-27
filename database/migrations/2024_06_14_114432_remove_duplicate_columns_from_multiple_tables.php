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
        Schema::table('credit_notes', function (Blueprint $table) {
            if (Schema::hasColumn('credit_notes', 'partner_name')) {
                $table->dropColumn('partner_name');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'plan_name')) {
                $table->dropColumn('plan_name');
            }

            if (Schema::hasColumn('invoices', 'price')) {
                $table->dropColumn('price');
            }
        });

        Schema::table('partner_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('partner_addresses', 'full_name')) {
                $table->dropColumn('full_name');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'plan_name')) {
                $table->dropColumn('plan_name');
            }

            if (Schema::hasColumn('subscriptions', 'plan_price')) {
                $table->dropColumn('plan_price');
            }

            if (Schema::hasColumn('subscriptions', 'partner_name')) {
                $table->dropColumn('partner_name');
            }

            if (Schema::hasColumn('subscriptions', 'company_name')) {
                $table->dropColumn('company_name');
            }
        });

        Schema::table('supports', function (Blueprint $table) {
            if (Schema::hasColumn('supports', 'partner_email')) {
                $table->dropColumn('partner_email');
            }
        });

        Schema::table('terms', function (Blueprint $table) {
            if (Schema::hasColumn('terms', 'partner_name')) {
                $table->dropColumn('partner_name');
            }
        });

        Schema::dropIfExists('enterprises');

        Schema::dropIfExists('hosted_page_ids');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('credit_notes', 'partner_name')) {
                $table->string('partner_name');
            }
        });


        Schema::table('invoices', function (Blueprint $table) {
            $table->string('plan_name');
            $table->string('price');
        });

        Schema::table('partner_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('partner_addresses', 'full_name')) {
                $table->string('full_name');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('plan_name');
            $table->string('plan_price');
            $table->string('partner_name');
            $table->string('company_name');
        });

        Schema::table('supports', function (Blueprint $table) {
            if (!Schema::hasColumn('supports', 'partner_email')) {
                $table->string('partner_email');
            }
        });

        Schema::table('terms', function (Blueprint $table) {
            if (!Schema::hasColumn('terms', 'partner_name')) {
                $table->string('partner_name');
            }
        });

        if (!Schema::hasTable('enterprises')) {

            Schema::create('hosted_page_ids', function (Blueprint $table) {
                $table->id();
                $table->string('hostedpage_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('enterprises')) {

            Schema::create('enterprises', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('plan_code');
                $table->string('recurring_price');
                $table->string('partner_id');
                $table->timestamps();
            });
        }
    }
};
