<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriptions extends Model
{
    use HasFactory;

    protected $casts = [
        'line_items' => 'json',
    ];

    public function dowgradeToFreePlan($access_token, $paymentMethod, $free_plan, $subscription)
    {
        $app_url = env('APP_URL');

        $update_url = env('UPDATE_FREE_SUBSCRIPTION_URL');

        $client = new \GuzzleHttp\Client();

        $options = [
            'headers' => [
                'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                'content-type: application/json'
            ],
            'json' => [
                "card_id" => $paymentMethod->payment_method_id,
                "plan" => [
                    "plan_code" => $free_plan->plan_code,
                    "plan_description" => $free_plan->plan_description,
                    "price" => $free_plan->price,
                    "quantity" => 1
                ],
                "billing_cycles" => -1,
                "auto_collect" => true,
                "customer_id" => $subscription->zoho_cust_id,
            ]
        ];

        $update_subscription_url =  $update_url . $subscription->subscription_id;


        $res = $client->request(
            'PUT',
            $update_subscription_url,
            $options
        );

        $response = (string) $res->getBody();
        $response = json_decode($response);

        $subscriptions = $response->subscription;

        $subscription->subscription_id = $subscriptions->subscription_id;

        $subscription->subscription_number = $subscriptions->subscription_number;

        $subscription->status = $subscriptions->status;

        $subscription->start_date = $subscriptions->current_term_starts_at;

        $subscription->next_billing_at = $subscriptions->next_billing_at;

        $subscription->plan_id = $free_plan->plan_id;

        $subscription->save();
    }

    public function cancelSubscription($access_token, $subscription)
    {
        $subscription_url = env('SUBSCRIPTION_URL');
        $client = new \GuzzleHttp\Client();

        $options = [
            'headers' => [
                'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                'content-type: application/json'
            ]
        ];

        $cancel_subscription_url = $subscription_url . $subscription->subscription_id . '/cancel';

        $res = $client->request(
            'POST',
            $cancel_subscription_url,
            $options
        );

        $response = (string) $res->getBody();
        $response = json_decode($response);
        $subscription->status = $response->subscription->status;
        $subscription->save();
    }
}
