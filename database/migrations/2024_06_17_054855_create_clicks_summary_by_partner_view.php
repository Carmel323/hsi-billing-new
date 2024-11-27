<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateClicksSummaryByPartnerView extends Migration
{
    public function up()
    {
        if (Schema::hasView('clicks_summary_by_partner')) {

            DB::statement('DROP VIEW clicks_summary_by_partner');
        }
        DB::statement("
            CREATE VIEW clicks_summary_by_partner AS
            SELECT
                partner_id,
                DATE(click_ts) AS click_date,
                WEEK(click_ts) AS click_week,
                MONTH(click_ts) AS click_month,
                YEAR(click_ts) AS click_year,
                intended_zip,
                intended_city,
                intended_state,
                COUNT(*) AS click_count
            FROM
                clicks
            GROUP BY
                partner_id,
                click_date,
                click_week,
                click_month,
                click_year,
                intended_zip,
                intended_city,
                intended_state;
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS clicks_summary_by_partner;");
    }
}
