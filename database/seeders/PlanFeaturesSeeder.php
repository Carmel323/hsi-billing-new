<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;

class PlanFeaturesSeeder extends Seeder
{
    public function run()
    {
        $planFeatures = [
            [
                'plan_code' => 'basic-monthly',
                'features_json' => [
                    'update_logo' => true,
                    'custom_url' => true,
                    'zip_code_availability_updates' => true,
                    'data_updates' => true,
                    'self_service_portal_access' => true,
                    'account_management_support' => false,
                    'reporting' => 'Monthly',
                    'maximum_allowed_clicks' => 'up to 1,000/month',
                    'maximum_click_monthly_add_on' => '$500 for 500 Clicks (limit of 1,500 total clicks/mo)'
                ],
            ],
            [
                'plan_code' => 'growth-monthly',
                'features_json' => [
                    'update_logo' => true,
                    'custom_url' => true,
                    'zip_code_availability_updates' => true,
                    'data_updates' => true,
                    'self_service_portal_access' => true,
                    'account_management_support' => true,
                    'reporting' => 'Weekly',
                    'maximum_allowed_clicks' => 'up to 1,250/month',
                    'maximum_click_monthly_add_on' => '$1,500 for 750 clicks (limit of 2,000 total clicks/mo)'
                ],
            ],
            [
                'plan_code' => 'enterprise',
                'features_json' => json_encode([
                    'update_logo' => true,
                    'custom_url' => true,
                    'zip_code_availability_updates' => true,
                    'data_updates' => true,
                    'self_service_portal_access' => true,
                    'account_management_support' => true,
                    'reporting' => 'Daily',
                    'maximum_allowed_clicks' => 'Over 2,000/month',
                    'maximum_click_monthly_add_on' => ''
                ])
            ],
        ];

        foreach ($planFeatures as $planFeature) {
            
            Feature::firstOrCreate(
                ['plan_code' => $planFeature['plan_code']],
                ['features_json' => $planFeature['features_json']]
            );
        }
    }
}
