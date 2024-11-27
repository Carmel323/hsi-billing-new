<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ModifyPartnerClicksViewAdditions extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW partner_clicks_view AS
            SELECT 
                p.id AS partner_id,
                p.company_name AS partner_company_name,
                pu.email AS primary_contact_email,
                pl.plan_code AS subscribed_plan_name,
                s.addon AS subscribed_addon_name,
                pl.max_clicks AS plan_max_clicks,
                ao.max_clicks AS addon_max_clicks,
                MONTH(c.click_ts) AS click_month,
                YEAR(c.click_ts) AS click_year,
                COUNT(DISTINCT c.click_id) AS unique_clicks_count,
                (pl.max_clicks + COALESCE(ao.max_clicks, 0)) AS total_max_clicks,
                CASE 
                    WHEN (pl.max_clicks + COALESCE(ao.max_clicks, 0)) > 0 
                    THEN ((COUNT(DISTINCT c.click_id) / (pl.max_clicks + COALESCE(ao.max_clicks, 0)))*100)
                    ELSE 0
                END AS clicks_usage_percentage
            FROM 
                clicks c
            JOIN 
                partners_affiliates pa ON c.partners_affiliates_id = pa.id
            JOIN 
                partners p ON pa.partner_id = p.id
            JOIN 
                partner_users pu ON p.zoho_cust_id = pu.zoho_cust_id AND pu.is_primary = 1
            JOIN 
                subscriptions s ON p.zoho_cust_id = s.zoho_cust_id
            JOIN 
                plans pl ON s.plan_id = pl.plan_id
            LEFT JOIN 
                add_ons ao ON s.addon = ao.addon_code
            GROUP BY 
                p.id, p.company_name, pu.email, pl.plan_code, s.addon, pl.max_clicks, ao.max_clicks, click_month, click_year
            ORDER BY 
                click_year DESC, click_month DESC;
        ");
    }

    public function down()
    {
        // Optionally, you can define what happens when this migration is rolled back.
        DB::statement('DROP VIEW IF EXISTS partner_clicks_view');
    }
}
