<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreatePartnerClicksView extends Migration
{
    public function up()
    {
        DB::statement("
    CREATE OR REPLACE VIEW partner_clicks_view AS
    SELECT 
        p.zoho_cust_id AS partner_zoho_cust_id,
        pu.zoho_cpid AS partner_user_zoho_cpid,
        p.company_name AS partner_company_name,
        pu.email AS primary_contact_email,
        pl.plan_name AS subscribed_plan_name,
        s.addon AS subscribed_addon_name,
        MONTH(c.click_ts) AS click_month,
        YEAR(c.click_ts) AS click_year,
        COUNT(c.id) AS clicks_count
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
    GROUP BY 
        p.zoho_cust_id, pu.zoho_cpid, p.company_name, pu.email, pl.plan_name, s.addon, click_month, click_year
");
    }


    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS partner_clicks_view");
    }
}
