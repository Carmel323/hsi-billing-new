<?php

namespace App\Http\Controllers;

use App\Models\AccessToken;
use App\Models\AddOn;
use Illuminate\Http\Request;
use App\Models\Feature;
use App\Models\Plans;
use GuzzleHttp\Exception\GuzzleException;

class PlanFeaturesController extends Controller
{
    public function showUpdateForm($planCode)
    {
        $feature = Feature::where('plan_code', $planCode)->first();

        $plan = Plans::where('plan_code', $planCode)->first();

        $addons = AddOn::where('plan_id', $plan->plan_id)->get();

        $existingFeatures = $feature->features_json ?? [];

        return view('admin.planfeatures', compact('feature', 'existingFeatures', 'planCode', 'plan', 'addons'));
    }

    public function update(Request $request)
    {
        $planCode = $request->input('plan_code');

        $features = [
            'update_logo' => $request->has('update_logo'),
            'custom_url' => $request->has('custom_url'),
            'zip_code_availability_updates' => $request->has('zip_code_availability_updates'),
            'data_updates' => $request->has('data_updates'),
            'self_service_portal_access' => $request->has('self_service_portal_access'),
            'account_management_support' => $request->has('account_management_support'),
            'reporting' => $request->input('reporting'),
            'maximum_allowed_clicks' => $request->input('maximum_allowed_clicks'),
            'maximum_click_monthly_add_on' => $request->input('maximum_click_monthly_add_on')
        ];

        Feature::updateOrCreate(['plan_code' => $planCode], ['features_json' => $features]);

        return redirect()->back()->with('success', 'Plan features updated successfully.');
    }

    public function updatePlanPrice(Request $request, AccessToken $token)
    {
        $plans_url = env('PLAN_URL');
        $token1 = AccessToken::latest('created_at')->first();
        if ($token1 === null) {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
        }
        $access_token = $token1->access_token;

        $plan = Plans::where('plan_id', $request->plan_id)->first();

        $client = new \GuzzleHttp\Client();

        try {
            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                ],
                'json' => [

                    "addon_code" => $plan->plan_code,
                    "name" => $plan->plan_name,
                    "recurring_price" => $request->plan_price
                ],

            ];

            $res = $client->request(
                'PUT',
                $plans_url . '/' . $plan->plan_code,
                $options
            );

            $response = (string) $res->getBody();
            $response = json_decode($response);
            $plans = $response->plan;

            $plan->price = $plans->recurring_price;
            $plan->save();

            return redirect()->back()->with('success', 'Plan Price updated successfully.');
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);
            $response = json_decode($response);

            if ($response->message === "You are not authorized to perform this operation") {
                $token->getToken();
                return back()->with('fail', 'Kindly Try To Add Again');
            }
        }
    }

    public function updateAddonPrice(Request $request, AccessToken $token)
    {
        $addon_url = env('ADDON_LIST');
        $token1 = AccessToken::latest('created_at')->first();
        if ($token1 === null) {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
        }
        $access_token = $token1->access_token;

        $addon = AddOn::where('addon_code', $request->addon_code)->first();

        $client = new \GuzzleHttp\Client();

        try {
            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                ],
                'json' => [

                    "addon_code" => $addon->addon_code,
                    "name" => $addon->name,
                    "price_brackets" => [
                        [
                            "price" => $request->addon_price,
                        ]

                    ]
                ],

            ];

            $res = $client->request(
                'PUT',
                $addon_url . '/' . $addon->addon_code,
                $options
            );

            $response = (string) $res->getBody();
            $response = json_decode($response);
            $addons = $response->addon;

            $addon->addon_price = $addons->price_brackets[0]->price;
            $addon->save();

            return redirect()->back()->with('success', 'Add-On Price updated successfully.');
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);
            $response = json_decode($response);

            if ($response->message === "You are not authorized to perform this operation") {
                $token->getToken();
                return back()->with('fail', 'Kindly Try To Add Again');
            }
        }
    }
}
