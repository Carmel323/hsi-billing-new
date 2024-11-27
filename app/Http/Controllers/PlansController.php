<?php

namespace App\Http\Controllers;

use App\Models\Plans;
use App\Models\Subscriptions;
use App\Models\AccessToken;
use App\Models\AddOn;
use App\Models\Partner;
use App\Models\Enterprise;
use App\Models\Product;
use App\Models\Feature;
use App\Models\ProviderAvailabilityData;
use App\Models\ProviderData;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class PlansController extends Controller
{

    //Update Plans
    public function updatePlans()
    {
        $plans_url = env('PLAN_URL');

        $token = AccessToken::latest('created_at')->first();

        $access_token = $token->access_token;


        $client = new \GuzzleHttp\Client();

        try {
            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                ]
            ];

            $res = $client->request(
                'GET',
                $plans_url,
                $options
            );

            $response = (string) $res->getBody();
            $response = json_decode($response);
            $plans = $response->plans;


            if ($plans) {
                $planed = Plans::All();
                $planed->each->delete();
                $addon = AddOn::All();
                $addon->each->delete();
            }

            foreach ($plans as $plan) {
                $is_cpc_plan = stripos($plan->plan_code, 'cpc') !== false;
                $plan_new = new Plans();

                $plan_new->plan_name = $plan->name;
                $plan_new->plan_id = $plan->plan_id;
                $plan_new->plan_code = $plan->plan_code;
                $plan_new->plan_description = $plan->description;
                $plan_new->price = $plan->recurring_price;
                $plan_new->quantity = "1";
                $plan_new->max_clicks = $plan->custom_fields[0]->value;
                if ($is_cpc_plan) {
                    $plan_new->is_cpc = true;
                }

                $addons = $plan->addons;
                foreach ($addons as $addon) {
                    $add_on = new AddOn();
                    $add_on->name = $addon->name;
                    $add_on->addon_code = $addon->addon_code;
                    $add_on->plan_id = $plan->plan_id;
                    $add_on->save();
                }

                $plan_new->save();
            }

            $addons =  AddOn::all();

            foreach ($addons as $addon) {
                $this->getAddon($token, $addon);
            }



            return back()->with('success', 'Plans added successfully');
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);
            $response = json_decode($response);

            if ($response->message === "You are not authorized to perform this operation") {
                $token->getToken();
                return redirect('/admin')->with('fail', 'Kindly try again, access token expired!');
            }
        }
    }


    //Get Addon
    public function getAddon($token, $addon)
    {

        $addon_url = env('ADDON_LIST');
        $token1 = AccessToken::latest('created_at')->first();
        if ($token1 === null) {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
        }
        $access_token = $token1->access_token;



        $client = new \GuzzleHttp\Client();

        try {
            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                ]
            ];

            $res = $client->request(
                'GET',
                $addon_url . '/' . $addon->addon_code,
                $options
            );

            $response = (string) $res->getBody();
            $response = json_decode($response);
            $addon_data = $response->addon;


            $addon->addon_price = $addon_data->price_brackets[0]->price;
            $addon->max_clicks = $addon_data->custom_fields[0]->value;
            $addon->save();
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);
            $response = json_decode($response);

            if ($response->message === "You are not authorized to perform this operation") {
                $token->getToken();
                return redirect('/admin')->with('fail', 'Kindly try again, access token expired!');
            }
        }
    }


    //Add Plans
    public function addPlans(Request $request, AccessToken $token)
    {
        $plans_url = env('PLAN_URL');
        $token1 = AccessToken::latest('created_at')->first();
        if ($token1 === null) {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
        }
        $access_token = $token1->access_token;

        $existingPlan = Plans::where('plan_code', $request->plan_code)->first();

        if ($existingPlan) {

            return redirect()->back()->with('fail', 'Plan code already exists. Please choose a different one.');
        }

        $this->getProductId($token);

        $product = Product::latest('created_at')->first();





        $client = new \GuzzleHttp\Client();

        try {
            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                ],
                'json' => [

                    "plan_code" => $request->plan_code,
                    "name" => $request->name,
                    "recurring_price" => $request->recurring_price,
                    "interval" => $request->interval,
                    "interval_unit" => $request->interval_unit,
                    "billing_cycles" => $request->billing_cycles,
                    "product_id" => $product->product_id,
                    "product_type" =>  "service",
                ],

            ];

            $res = $client->request(
                'POST',
                $plans_url,
                $options
            );

            $response = (string) $res->getBody();
            $response = json_decode($response);
            $plan = $response->plan;

            return redirect('/sync-plans');
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);
            $response = json_decode($response);

            if ($response->message === "You are not authorized to perform this operation") {
                $token->getToken();
                return redirect('/admin')->with('fail', 'Kindly Try To Add Again');
            }
        }
    }

    public function getPlan(AccessToken $token)
    {
        if (Session::has('loginPartner')) {

            $subscription = null;
            $plan_subscribed = null;
            $plan_hasaddon = null;
            $is_subscription_enterprise_plan = false;
            $number_of_addons = null;
            $addons = null;

            $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();
            $showModal = false;

            $availability_data = ProviderAvailabilityData::where('zoho_cust_id', Session::get('loginId'))->first();
            $company_info = ProviderData::where('zoho_cust_id', Session::get('loginId'))->first();
            $partner_plans = $partner->selected_plans ?? null;
            $selected_plans = json_decode($partner_plans);

            if ($availability_data === null || $company_info === null) {
                $showModal = true;
            }

            if ($selected_plans === null) {
                return redirect('/no-selected-plans');
            }

            if (Session::has('loginId')) {

                $subscription = DB::table('subscriptions')
                    ->join('plans', 'subscriptions.plan_id', '=', 'plans.plan_id')
                    ->select('plans.*', 'subscriptions.*')
                    ->where('zoho_cust_id', '=', Session::get('loginId'))
                    ->where('status', 'live')
                    ->first();

                $subscribed_id = isset($subscription->plan_id) ? $subscription->plan_id : '';
                if ($subscribed_id) {

                    $plan_subscribed = Plans::where('plan_id', '=', $subscribed_id)->first();

                    if ($plan_subscribed) {

                        $is_subscription_enterprise_plan = stripos($plan_subscribed->plan_code, 'enterprise') !== false;

                        $plan_hasaddon = AddOn::where('plan_id', $plan_subscribed->plan_id)->first();

                        $number_of_addons = AddOn::where('plan_id', $plan_subscribed->plan_id)->count();

                        $addons = AddOn::where('plan_id', $plan_subscribed->plan_id)->get();
                    }
                }
            }

            $plans = Plans::join('features', 'plans.plan_code', '=', 'features.plan_code')
                ->select('plans.*', 'features.features_json')
                ->whereIn('plans.plan_id', $selected_plans)
                ->get();


            $plans = $plans->map(function ($plan) use ($subscription) {

                $is_upgrade_possible = $subscription ? $subscription->price < $plan->price : false;
                $is_current_plan = $subscription && $subscription->plan_id == $plan->plan_id;
                $features = json_decode($plan->features_json, true);
                $is_enterprise_plan = stripos($plan->plan_code, 'enterprise') !== false;

                if ($is_enterprise_plan && !$is_current_plan) {
                    return null;
                }

                return [
                    'plan_id' => $plan->plan_id,
                    'plan_name' => $plan->plan_name,
                    'plan_code' => $plan->plan_code,
                    'price' => $plan->price,
                    'is_current_plan' => $is_current_plan,
                    'is_upgrade_possible' => $is_upgrade_possible,
                    'is_enterprise_plan' => $is_enterprise_plan,
                    'current_subs_status' => $is_current_plan && $subscription ? $subscription->status : false,
                    'features' => $features,
                    'addon' => $is_current_plan && $subscription ? $subscription->addon : false,
                    'next_billing_at' => $is_current_plan && $subscription ? Carbon::parse($subscription->next_billing_at)->format('d-M-Y') : null
                ];
            })->filter(); // Remove null values from the collection

            // Sort plans by price in ascending order
            $plans = $plans->sortBy('price')->values();

            $defaultEnterprisePlan = [
                'plan_id' => 'custom_enterprise',
                'plan_name' => 'Custom Enterprise',
                'plan_code' => 'enterprise',
                'price' => 0,
                'is_current_plan' => false,
                'is_upgrade_possible' => false,
                'is_enterprise_plan' => true,
                'current_subs_status' => false,
                'features' => [
                    'update_logo' => true,
                    'custom_url' => true,
                    'zip_code_availability_updates' => true,
                    'data_updates' => true,
                    'self_service_portal_access' => true,
                    'account_management_support' => true,
                    'reporting' => 'Daily',
                    'maximum_allowed_clicks' => 'Over 2,000/month',
                    'maximum_click_monthly_add_on' => ''
                ],
                'addon' => false,
                'next_billing_at' => null,
            ];

            if (!$is_subscription_enterprise_plan && in_array('custom_plans', $selected_plans)) {
                $plans->push($defaultEnterprisePlan);
            }

            return view('partner.plans', compact('plans', 'subscription', 'plan_subscribed', 'plan_hasaddon', 'number_of_addons', 'addons', 'showModal', 'availability_data', 'company_info'));
        }
    }

    public function getPlanOld(AccessToken $token)
    {
        if (Session::has('loginPartner')) {

            $subscription = null;

            $plan_subscribed = null;

            $plan_hasaddon = null;

            $is_subscription_enterprise_plan = false;

            $number_of_addons = null;

            $addons = null;

            $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();

            $showModal = false;

            $availability_data = ProviderAvailabilityData::where('zoho_cust_id', Session::get('loginId'))->first();
            $company_info = ProviderData::where('zoho_cust_id', Session::get('loginId'))->first();
            $partner_plans = $partner->selected_plans ?? null;
            $selected_plans = json_decode($partner_plans);


            if ($availability_data === null || $company_info === null) {
                $showModal = true;
            }

            // if (!$partner->is_approved) {
            //     return redirect('/logout')->with('fail', 'Your provider info is not yet approved');
            // }

            if ($selected_plans === null) {
                return redirect('/no-selected-plans');
            }

            if (Session::has('loginId')) {

                $subscription =  DB::table('subscriptions')
                    ->join('plans', 'subscriptions.plan_id', '=', 'plans.plan_id')
                    ->select('plans.*', 'subscriptions.*')
                    ->where('zoho_cust_id', '=', Session::get('loginId'))
                    ->where('status', 'live')
                    ->first();

                $subscribed_id = isset($subscription->plan_id) ? $subscription->plan_id : '';
                if ($subscribed_id) {

                    $plan_subscribed = Plans::where('plan_id', '=', $subscribed_id)->first();

                    if ($plan_subscribed) {

                        $is_subscription_enterprise_plan = stripos($plan_subscribed->plan_code, 'enterprise') !== false;

                        $plan_hasaddon = AddOn::where('plan_id', $plan_subscribed->plan_id)->first();

                        $number_of_addons = AddOn::where('plan_id', $plan_subscribed->plan_id)->count();

                        $addons = AddOn::where('plan_id', $plan_subscribed->plan_id)->get();
                    }
                }
            }


            $plans = Plans::join('features', 'plans.plan_code', '=', 'features.plan_code')
                ->select('plans.*', 'features.features_json')
                ->whereIn('plans.plan_id', $selected_plans)
                ->get();



            $plans = $plans->map(function ($plan) use ($subscription) {

                $is_upgrade_possible = $subscription ? $subscription->price < $plan->price : false;
                $is_current_plan = $subscription && $subscription->plan_id == $plan->plan_id;
                $features = json_decode($plan->features_json, true);
                $is_enterprise_plan = stripos($plan->plan_code, 'enterprise') !== false;

                if ($is_enterprise_plan && !$is_current_plan) {
                    return null;
                }


                return [
                    'plan_id' => $plan->plan_id,
                    'plan_name' => $plan->plan_name,
                    'plan_code' => $plan->plan_code,
                    'price' => $plan->price,
                    'is_current_plan' => $is_current_plan,
                    'is_upgrade_possible' => $is_upgrade_possible,
                    'is_enterprise_plan' => $is_enterprise_plan,
                    'current_subs_status' => $is_current_plan && $subscription ? $subscription->status : false,
                    'features' => $features,
                    'addon' => $is_current_plan && $subscription ? $subscription->addon : false,
                    'next_billing_at' => $is_current_plan && $subscription ? Carbon::parse($subscription->next_billing_at)->format('d-M-Y') : null
                ];
            })->filter();  // Remove null values from the collection


            $defaultEnterprisePlan = [
                'plan_id' => 'custom_enterprise',
                'plan_name' => 'Custom Enterprise',
                'plan_code' => 'enterprise',
                'price' => 0,
                'is_current_plan' => false,
                'is_upgrade_possible' => false,
                'is_enterprise_plan' => true,
                'current_subs_status' => false,
                'features' => [
                    'update_logo' => true,
                    'custom_url' => true,
                    'zip_code_availability_updates' => true,
                    'data_updates' => true,
                    'self_service_portal_access' => true,
                    'account_management_support' => true,
                    'reporting' => 'Daily',
                    'maximum_allowed_clicks' => 'Over 2,000/month',
                    'maximum_click_monthly_add_on' => ''
                ],
                'addon' => false,
                'next_billing_at' => null,
            ];


            if (!$is_subscription_enterprise_plan && in_array('custom_plans', $selected_plans)) {

                $plans->push($defaultEnterprisePlan);
            }



            return view('partner.plans', compact('plans', 'subscription', 'plan_subscribed', 'plan_hasaddon', 'number_of_addons', 'addons', 'showModal', 'availability_data', 'company_info'));
        }
    }


    public function getPlanV2Old(AccessToken $token)
    {
        if (Session::has('loginPartner')) {

            $enterprise = Enterprise::where('zoho_cust_id', '=', Session::get('loginId'))->first();

            $plan_enterprise = null;

            if ($enterprise) {
                $plan_enterprise =  Plans::where('plan_name', '=', 'Enterprise')
                    ->where('plan_code', '=', $enterprise->plan_code)
                    ->first();
            }

            $plan_subscribed = null;

            $subscriptionlive = null;

            if (Session::has('loginPartner')) {

                $plans = Plans::where('plan_name', '!=', 'Enterprise')

                    ->where('price', '!=', '0')->get();

                if (Session::has('loginId')) {

                    $subscriptionlive =  Subscriptions::where('zoho_cust_id', '=', Session::get('loginId'))
                        ->where('status', '=', 'live')
                        ->first();
                }
                if ($subscriptionlive) {
                    if (!empty($subscriptionlive->plan_id)) {
                        $subscribed_id = $subscriptionlive->plan_id;

                        $plan_subscribed = Plans::where('plan_id', '=', $subscribed_id)->first();
                    }
                }

                $subscription = Subscriptions::where('zoho_cust_id', Session::get('loginId'))->first();

                return view('partner.plans', compact('plans', 'plan_subscribed', 'plan_enterprise', 'subscriptionlive', 'subscription'));
            }
        }
    }

    public function getProductId($token)
    {
        $product_url = env('PRODUCT_URL');
        $token1 = AccessToken::latest('created_at')->first();
        if ($token1 === null) {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
        }
        $access_token = $token1->access_token;
        $client = new \GuzzleHttp\Client();

        try {
            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                ]
            ];

            $res = $client->request(
                'GET',
                $product_url,
                $options
            );

            $response = (string) $res->getBody();
            $response = json_decode($response);
            $products = $response->products;
            foreach ($products as $product) {
                $products = new Product();
                $products->product_id = $product->product_id;
                $products->save();
            }
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);
            $response = json_decode($response);

            if ($response->message === "You are not authorized to perform this operation") {
                $token->getToken();
                return redirect('/')->with('fail', 'Kindly try again, access token expired!');
            }
        }
    }

    public function changePlan()
    {
        $plan_id = Route::getCurrentRoute()->id;

        $subscription =  DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.plan_id')
            ->select('plans.*', 'subscriptions.*')
            ->where('zoho_cust_id', '=', Session::get('loginId'))
            ->first();

        $plan = Plans::where('plan_id', $plan_id)->first();
        $showModal = false;

        return view('partner.change-plan', compact('subscription', 'plan', 'showModal'));
    }

    public function subscribePlan()
    {
        $plan_id = Route::getCurrentRoute()->id;

        $plan = Plans::where('plan_id', $plan_id)->first();

        $showModal = false;

        return view('partner.subscribe-plan', compact('plan', 'showModal'));
    }

    public function addonPlan()
    {
        $plan_id = Route::getCurrentRoute()->id;

        $addon = AddOn::where('plan_id', $plan_id)->first();

        $subscription =  DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.plan_id')
            ->select('plans.*', 'subscriptions.*')
            ->where('zoho_cust_id', '=', Session::get('loginId'))
            ->first();

        $showModal = false;
        return view('partner.addon-plan', compact('addon', 'subscription', 'showModal'));
    }




    public function addAddon(Request $request, AccessToken $token)
    {

        $addon_url = env('ADDON_LIST');
        $token1 = AccessToken::latest('created_at')->first();
        if ($token1 === null) {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
        }
        $access_token = $token1->access_token;

        $existing_add_on = AddOn::where('addon_code', $request->addon_code)->first();

        if ($existing_add_on) {
            return back()->with('fail', 'Add-On Name already exists');
        } else {
            $this->getProductId($token);

            $product = Product::latest('created_at')->first();

            $plan = Plans::where('plan_code', $request->plan_code)->first();


            $client = new \GuzzleHttp\Client();

            try {
                $options = [
                    'headers' => [
                        'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                        'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                    ],
                    'json' => [

                        "addon_code" => $request->addon_code,
                        "name" => $request->addon_name,
                        "price_brackets" => [
                            [
                                "price" => $request->addon_price,
                            ]
                        ],
                        "applicable_to_all_plans" => false,
                        "plans" => [
                            [
                                "plan_code" => $request->plan_code,
                            ]
                        ],
                        "type" => "one_time",
                        "product_id" => $product->product_id,
                        "product_type" =>  "service",
                    ],

                ];

                $res = $client->request(
                    'POST',
                    $addon_url,
                    $options
                );

                $response = (string) $res->getBody();
                $response = json_decode($response);
                $addon = $response->addon;
                $add_on = new AddOn();
                $add_on->name = $addon->name;
                $add_on->addon_price = $addon->price_brackets[0]->price;
                $add_on->addon_code = $addon->addon_code;
                $addon->max_clicks = $addon->custom_fields[0]->value;
                $add_on->plan_id = $plan->plan_id;
                $add_on->save();

                return redirect()->back()->with('success', 'Addon added successfully');
            } catch (GuzzleException $e) {

                $response = $e->getResponse()->getBody(true);
                $response = json_decode($response);

                if ($response->message === "You are not authorized to perform this operation") {
                    $token->getToken();
                    return redirect('/admin')->with('fail', 'Kindly Try To Add Again');
                }
            }
        }
    }
    public function subscribeCustomPlan()
    {
        $plan_id = Route::getCurrentRoute()->id;
        $hostedpageId = Route::getCurrentRoute()->hostedpageId;
        $partnerId = Route::getCurrentRoute()->partnerId;

        $subscription = Subscriptions::where('zoho_cust_id', $partnerId)->where('plan_id', $plan_id)->first();
        $showModal = false;

        if ($subscription) {
            echo ('<h3>It seems like you have already used this page. Please contact your administrator for further assistance</h3>');
        } else {

            $hostedpageUrl = "https://billing.zoho.com/hostedpage/" . $hostedpageId . "/checkout";

            $plan = Plans::where('plan_id', $plan_id)->first();

            return view('partner.subscribe-custom-plan', compact('plan', 'hostedpageUrl', 'showModal'));
        }
    }
}
