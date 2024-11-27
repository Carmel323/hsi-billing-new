<?php

namespace App\Http\Controllers;

use App\Models\AccessToken;
use App\Models\HostedPageId;
use App\Models\Subscriptions;
use App\Models\Partner;
use App\Models\PaymentMethod;
use App\Models\Enterprise;
use App\Models\Plans;
use App\Models\Support;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Mail\CreateSubscription;
use App\Models\AddOn;
use App\Models\Card;
use App\Models\PartnerUsers;
use App\Models\ProviderAvailabilityData;
use App\Models\ProviderData;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionsController extends Controller
{
    //Get subscription

    public function getSubscription()
    {
        if (Session::has('loginPartner')) {

            $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();
            $subscriptionlive = null;
            $subscriptions = null;
            $paymentmethod = null;
            $partner_plans = $partner->selected_plans ?? null;
            $selected_plans = json_decode($partner_plans);
            $highest_plan = Plans::query()
                ->orderBy('price', 'desc')
                ->whereIn('plan_id', $selected_plans)
                ->first();
            $lowest_plan = Plans::query()
                ->orderBy('price', 'asc')
                ->where('price', '!=', '0')
                ->whereIn('plan_id', $selected_plans)
                ->first();

            if (Session::has('loginId')) {

                $subscriptionlive = Subscriptions::where('zoho_cust_id', '=', Session::get('loginId'))
                    ->where('status', '=', 'live')
                    ->first();

                $subscriptions = DB::table('subscriptions')
                    ->join('plans', 'subscriptions.plan_id', '=', 'plans.plan_id')
                    ->select('plans.*', 'subscriptions.*')
                    ->where('zoho_cust_id', '=', Session::get('loginId'))
                    ->get();

                $paymentmethod = PaymentMethod::where('zoho_cust_id', '=', Session::get('loginId'))->first();
            }

            if ($subscriptionlive && !empty($subscriptionlive->plan_id)) {

                $subscribed_id = $subscriptionlive->plan_id;
                $plan_subscribed = Plans::where('plan_id', '=', $subscribed_id)->first();
                $plan_subscribed_id = $plan_subscribed->id ?? null;
                $current_plan = Plans::where('plan_id', $subscriptionlive->plan_id)->first();
                $is_enterprise_plan = $current_plan ? stripos($current_plan->plan_code, 'enterprise') !== false : false;
                $current_plan_has_addon = $current_plan ? AddOn::where('plan_id', $current_plan->plan_id)->exists() : false;
            } else {
                $current_plan = null;
                $plan_subscribed = null;
                $plan_subscribed_id = null;
                $is_enterprise_plan = false;
                $current_plan_has_addon = false;
            }


            if ($plan_subscribed) {

                $plans_orders = Plans::query()
                    ->orderBy('price', 'desc')
                    ->where('plan_code', 'NOT LIKE', '%' . 'enterprise' . '%')
                    ->where('price', '>', $plan_subscribed->price)
                    ->whereIn('plan_id', $selected_plans)
                    ->get();

                $plans_ascs = Plans::query()
                    ->orderBy('id', 'asc')
                    ->where('price', '!=', '0')
                    ->where('price', '<', $plan_subscribed->price)
                    ->whereIn('plan_id', $selected_plans)
                    ->get();
            } else {
                $plans_orders = Plans::query()
                    ->orderBy('price', 'desc')
                    ->where('plan_code', 'NOT LIKE', '%' . 'enterprise' . '%')
                    ->whereIn('plan_id', $selected_plans)
                    ->get();

                $plans_ascs = Plans::query()
                    ->orderBy('id', 'asc')
                    ->where('price', '!=', '0')
                    ->whereIn('plan_id', $selected_plans)
                    ->get();
            }

            $plans_orders = $plans_orders->sortBy('price')->values();

            $plans_ascs = $plans_ascs->sortBy('price')->values();

            $plans = Plans::where('price', '!=', '0')->get();

            $showModal = false;

            $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();

            $availability_data = ProviderAvailabilityData::where('zoho_cust_id', Session::get('loginId'))->first();

            $company_info = ProviderData::where('zoho_cust_id', Session::get('loginId'))->first();

            // Uncomment if you want to show modal based on availability_data or company_info being null
            // if ($availability_data === null || $company_info === null) {
            //     $showModal = true;
            // }

            return view('partner.subscriptions', compact(
                'subscriptions',
                'paymentmethod',
                'plan_subscribed',
                'plans',
                'highest_plan',
                'lowest_plan',
                'current_plan',
                'subscriptionlive',
                'plans_orders',
                'plans_ascs',
                'current_plan_has_addon',
                'is_enterprise_plan',
                'showModal',
                'availability_data',
                'company_info'
            ));
        }
    }

    public function getSubscriptionOld()
    {

        if (Session::has('loginPartner')) {
            $plans_order = Plans::query()
                ->orderBy('price', 'desc')
                ->where('plan_code', 'NOT LIKE', '%' . 'enterprise' . '%')
                ->first();


            $plans_asc = Plans::query()
                ->orderBy('id', 'asc')
                ->where('price', '!=', '0')
                ->first();


            if (Session::has('loginId')) {
                $subscriptionlive =  Subscriptions::where('zoho_cust_id', '=', Session::get('loginId'))
                    ->where('status', '=', 'live')
                    ->first();

                $subscriptions = DB::table('subscriptions')
                    ->join('plans', 'subscriptions.plan_id', '=', 'plans.plan_id')
                    ->select('plans.*', 'subscriptions.*')
                    ->where('zoho_cust_id', '=', Session::get('loginId'))
                    ->get();
                $paymentmethod = PaymentMethod::where('zoho_cust_id', '=', Session::get('loginId'))->first();
            }
            if (!empty($subscriptionlive->plan_id)) {

                $subscribed_id = $subscriptionlive->plan_id;
                $plan_subscribed = Plans::where('plan_id', '=', $subscribed_id)->first();
                $plan_subscribed_id = $plan_subscribed->id;
                $current_plan = Plans::where('plan_id', $subscriptionlive->plan_id)->first();
                $is_enterprise_plan = stripos($current_plan->plan_code, 'enterprise') !== false;
                $current_plan_has_addon = AddOn::where('plan_id', $current_plan->plan_id)->exists();
            } else {
                $current_plan = null;
                $plan_subscribed = null;
                $plan_subscribed_id = null;
                $is_enterprise_plan = false;
                $current_plan_has_addon = false;
            }
            $plans_orders = Plans::query()
                ->orderBy('price', 'desc')
                ->where('plan_code', 'NOT LIKE', '%' . 'enterprise' . '%')
                ->where('price', '>', $plan_subscribed->price)
                ->get();

            $plans_orders = $plans_orders->sortBy('price')->values();


            $plans_ascs = Plans::query()
                ->orderBy('id', 'asc')
                ->where('price', '!=', '0')
                ->where('price', '<', $plan_subscribed->price)
                ->get();


            $plans_ascs = $plans_ascs->sortBy('price')->values();

            $plans = Plans::where('price', '!=', '0')->get();

            $showModal = false;

            $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();

            $availability_data = ProviderAvailabilityData::where('zoho_cust_id', Session::get('loginId'))->first();

            $company_info = ProviderData::where('zoho_cust_id', Session::get('loginId'))->first();

            // if ($availability_data === null || $company_info === null) {
            //     $showModal = true;
            // }

            return view('partner.subscriptions', compact('subscriptions', 'paymentmethod', 'plan_subscribed', 'plans', 'plan_subscribed_id', 'subscriptionlive',  'plans_order', 'plans_orders', 'plans_ascs', 'plans_asc', 'current_plan_has_addon', 'is_enterprise_plan', 'showModal', 'availability_data', 'company_info'));
        }
    }


    // Create subscription
    public function addSubscription(AccessToken $token)
    {
        $add_url = env('NEW_SUBSCRIPTION_URL');
        $app_url = env('APP_URL');
        $id = Route::getCurrentRoute()->id;
        $token1 = AccessToken::latest('created_at')->first();
        if ($token1 !== null) {
            $access_token = $token1->access_token;
        } else {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
        }
        $plan = Plans::where('plan_id', '=', $id)->first();
        $subscription = Subscriptions::where('zoho_cust_id', Session::get('loginId'))->first();

        if (Session::has('loginId')) {

            $partner =  Partner::where('zoho_cust_id', '=', Session::get('loginId'))->first();
        }
        if (!$subscription) {
            $client = new \GuzzleHttp\Client();

            // try {
            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                    'content-type: application/json'
                ],
                'json' => [
                    "customer_id" => $partner->zoho_cust_id,
                    "plan" => [
                        "plan_code" => $plan->plan_code,
                        "quantity" => 1

                    ],
                    "is_metered_billing" => false,
                    "redirect_url" => "$app_url/thankyou-create",
                ]
            ];

            $res = $client->request(
                'POST',
                $add_url,
                $options
            );

            $response = (string) $res->getBody();
            $response = json_decode($response);

            return redirect($response->hostedpage->url);
            // } catch (GuzzleException $e) {

            //     $response = $e->getResponse()->getBody(true);

            //     $response = json_decode($response);

            //     if ($response) {

            //         return redirect('/logout')->with('fail', 'You have to login first');
            //     }
            // }
        } else {
            if ($subscription->status === 'live') {
                return redirect('/subscription')->with('fail', 'You cannot make duplicate subscription');
            } elseif ($subscription->status === 'cancelled') {
                return redirect('/subscribe-update/' . $id);
            }
        }
    }


    //Update subscription
    public function updateSubscription()
    {
        $app_url = env('APP_URL');

        $update_url = env('UPDATE_SUBSCRIPTION_URL');

        $token = AccessToken::latest('created_at')->first();

        $access_token = $token->access_token;

        $id = Route::getCurrentRoute()->id;

        $plan = Plans::where('plan_id', '=', $id)->first();

        $subscription =   Subscriptions::where('zoho_cust_id', '=', Session::get('loginId'))->first();

        if ($id !== $subscription->plan_id) {
            try {

                $client = new \GuzzleHttp\Client();

                $options = [
                    'headers' => [
                        'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                        'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                        'content-type: application/json'
                    ],
                    'json' => [
                        "subscription_id" => $subscription->subscription_id,
                        "plan" => [
                            "plan_code" => $plan->plan_code,
                            // "plan_description" => $plan->plan_description,
                            // "price" => $plan->price,
                            "quantity" => 1
                        ],
                        "auto_collect" => true,
                        "redirect_url" =>  "$app_url/thankyou-update",
                    ]
                ];

                $res = $client->request(
                    'POST',
                    $update_url,
                    $options
                );

                $response = (string) $res->getBody();
                $response = json_decode($response);

                return redirect($response->hostedpage->url);
            } catch (GuzzleException $e) {

                $response = $e->getResponse()->getBody(true);
                $response = json_decode($response);

                if ($response) {

                    return redirect('/logout')->with('fail', 'You have to login first');
                }
            }
        } else {
            return redirect('/subscription')->with('fail', 'You cannot make duplicate subscription');
        }
    }

    public function upgradeSubscription(Request $request)
    {

        return redirect('/change-plan/' . $request->plan_id);
    }

    public function createSubscription(Request $request, AccessToken $token)

    {
        $partner = Partner::where('zoho_cust_id', '=', $request->partner_id)->first();

        $primary_user = PartnerUsers::where('zoho_cust_id', $partner->zoho_cust_id)->where('is_primary', true)->first();

        $add_url = env('NEW_SUBSCRIPTION_URL');

        $update_url = env('UPDATE_SUBSCRIPTION_URL');

        if (empty($partner->zoho_cust_id)) {

            return back()->with('fail', 'Create a Partner!');
        }

        $subscription = Subscriptions::where('zoho_cust_id', '=', $partner->zoho_cust_id)->where('status', 'live')->first();

        if (!empty($subscription)) {

            return back()->with('fail', 'Subscription already exists for the partner');
        }

        $plan_sub = Plans::where('plan_code', '=', $request->plan_code)->first();

        $app_url = env('APP_URL');

        $token1 = AccessToken::latest('created_at')->first();

        if ($token1 !== null) {

            $access_token = $token1->access_token;
        } else {

            $token->getToken();

            $token1 = AccessToken::latest('created_at')->first();
        }

        $subscription_non_renewing = Subscriptions::where('zoho_cust_id', '=', $partner->zoho_cust_id)->where('status', 'non_renewing')->first();

        if ($subscription_non_renewing) {

            $client = new \GuzzleHttp\Client();


            $plan_sub = Plans::where('plan_code', '=', $request->plan_code)->first();

            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                    'content-type: application/json'
                ],
                'json' => [
                    "subscription_id" => $subscription_non_renewing->subscription_id,
                    "plan" => [
                        "plan_code" => $plan_sub->plan_code,
                        // "plan_description" => $plan_sub->plan_description,
                        // "price" => $plan_sub->price,
                        "quantity" => 1
                    ],
                    "auto_collect" => true,
                    "redirect_url" =>  "$app_url/thankyou-update",
                ]
            ];

            $res = $client->request(
                'POST',
                $update_url,
                $options
            );

            $response = (string) $res->getBody();
            $response = json_decode($response);

            $hostedpage = $response->hostedpage;

            $pay_link = $app_url . "/subscribe-custom-plan/" . $plan_sub->plan_id . "/" . $hostedpage->hostedpage_id . "/" . $partner->zoho_cust_id;


            $partner_email = $primary_user->email;

            $partner_name = $primary_user->first_name;

            $plan_name = $plan_sub->plan_name;

            $plan_price = $plan_sub->price;

            try {

                Mail::to($partner_email)->send(new CreateSubscription($partner_email, $partner_name, $plan_name, $plan_price, $pay_link));

                return redirect('/admin/view-partner/' . $partner->id . '/subscriptions')->with('success', 'Subscription Mail sent successfully!');
            } catch (\Exception $e) {

                Log::error("Error sending subscription email: " . $e->getMessage());

                return redirect('/admin/view-partner/' . $partner->id . '/subscriptions')->with('fail', 'There was a problem sending the email. Please try again later.');
            }
        }

        try {
            $client = new \GuzzleHttp\Client();


            $plan_sub = Plans::where('plan_code', '=', $request->plan_code)->first();

            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                    'content-type: application/json'
                ],
                'json' => [
                    "customer_id" => $partner->zoho_cust_id,
                    "plan" => [
                        "plan_code" => $plan_sub->plan_code,
                        "quantity" => 1

                    ],
                    "redirect_url" => "$app_url/thankyou-create",
                ]
            ];

            $res = $client->request(
                'POST',
                $add_url,
                $options
            );

            $response = (string) $res->getBody();
            $response = json_decode($response);

            $hostedpage = $response->hostedpage;

            $pay_link = $app_url . "/subscribe-custom-plan/" . $plan_sub->plan_id . "/" . $hostedpage->hostedpage_id . "/" . $partner->zoho_cust_id;

            $partner_email = $primary_user->email;

            $partner_name = $primary_user->first_name;

            $plan_name = $plan_sub->plan_name;

            $plan_price = $plan_sub->price;

            try {

                Mail::to($partner_email)->send(new CreateSubscription($partner_email, $partner_name, $plan_name, $plan_price, $pay_link));

                return back()->with('success', 'Subscription Mail sent successfully!');
            } catch (\Exception $e) {

                Log::error("Error sending subscription email: " . $e->getMessage());

                return back()->with('fail', 'There was a problem sending the email. Please try again later.');
            }
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);
            $response = json_decode($response);

            if ($response->message === "You are not authorized to perform this operation") {
                $token->getToken();
                return redirect('/admin/support')->with('fail', 'Kindly Try Again');
            }
        }
    }

    public function updatePaymentMethod()
    {
        $app_url = env('APP_URL');

        $UPDATE_PAYMENT_METHOD_URL = env('UPDATE_PAYMENT_METHOD_URL');

        $token = AccessToken::latest('created_at')->first();

        $access_token = $token->access_token;

        $id = Route::getCurrentRoute()->id;

        try {

            $client = new \GuzzleHttp\Client();

            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                    'content-type: application/json'
                ],
                'json' => [
                    "subscription_id" => $id,
                    "auto_collect" => true,
                    "redirect_url" =>  "$app_url/thankyou-update",
                ]
            ];

            $res = $client->request(
                'POST',
                $UPDATE_PAYMENT_METHOD_URL,
                $options
            );

            $response = (string) $res->getBody();
            $response = json_decode($response);

            return redirect($response->hostedpage->url);
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);
            $response = json_decode($response);

            if ($response->message === "You are not authorized to perform this operation") {

                $token->getToken();

                return back()->with('fail', 'Kindly Try Again!');
            }
        }
    }

    public function cancelSubscription(AccessToken $token, Subscriptions $subscriptions)
    {
        $token1 = AccessToken::latest('created_at')->first();

        if ($token1 !== null) {

            $access_token = $token1->access_token;
        } else {

            $token->getToken();

            $token1 = AccessToken::latest('created_at')->first();

            return back()->with('fail', 'Kindly Try Again');
        }
        $zoho_cust_id = Route::getCurrentRoute()->id;

        $subscription_url = env('SUBSCRIPTION_URL');

        $subscription = Subscriptions::where('status', 'live')->where('zoho_cust_id', $zoho_cust_id)->first();

        $subscriptions->cancelSubscription($access_token, $subscription);

        $support = Support::where('request_type', 'Cancellation')->where('status', 'open')->where('zoho_cust_id', $zoho_cust_id)->first();

        $support->status = "Completed";
        $support->comments = "Unable to revoke";
        $support->save();

        return redirect('/admin/support');
    }



    public function deletePaymentMethod(AccessToken $token)
    {
        $id = Route::getCurrentRoute()->id;
        $app_url = env('APP_URL');
        $partner_url = env('PARTNER_URL');
        $partner = Partner::where('zoho_cust_id', $id)->first();
        $payment_method = PaymentMethod::where('zoho_cust_id', $id)->first();
        $token1 = AccessToken::latest('created_at')->first();
        if ($token1 !== null) {
            $access_token = $token1->access_token;
        } else {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
            return back()->with('fail', 'Kindly Try Again');
        }

        $subscription = Subscriptions::where('zoho_cust_id', $id)->where('status', 'live')->first();

        if ($subscription) {
            return back()->with('fail', 'You cannot remove as you have associated subscription');
        }

        $client = new \GuzzleHttp\Client();

        try {
            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                ]
            ];


            $res = $client->request(
                'DELETE',
                $partner_url . '/' . $partner->zoho_cust_id . '/cards/' . $payment_method->payment_method_id,
                $options
            );

            $response = (string) $res->getBody();
            $response = json_decode($response);
            $payment_method->delete();
            return redirect('/admin/view-partner/' . $partner->id)->with('success', 'Payment method deleted successfully');
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);
            $response = json_decode($response);

            if ($response->message === "You are not authorized to perform this operation") {

                $token->getToken();

                return redirect('/')->with('fail', 'Kindly Subscribe Again!');
            }
        }
    }

    public function updateMeteredBilling(Request $request, AccessToken $token)
    {

        $token1 = AccessToken::latest('created_at')->first();
        if ($token1 === null) {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
        }
        $access_token = $token1->access_token;

        $subscription = Subscriptions::where('subscription_id', $request->subscription_id)->first();

        $subscription_url = env('UPDATE_FREE_SUBSCRIPTION_URL');


        // Update your logic here (e.g., save to the database)
        $meteredBillingEnabled = $request->metered_billing ?? "1";

        if ($meteredBillingEnabled) {
            $url = $subscription_url . $request->subscription_id . "/meteredbilling/enable";
        } else {
            $url = $subscription_url . $request->subscription_id . "/meteredbilling/disable";
        }

        $client = new \GuzzleHttp\Client();

        try {
            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                ],
            ];



            $res = $client->request(
                'POST',
                $url,
                $options
            );

            $response = (string) $res->getBody();

            $response = json_decode($response);

            $subscription->is_metered_billing = $response->subscription->is_metered_billing;

            $subscription->save();

            return back()->with('success', 'Metered Billing Status has been changed successfully');
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
