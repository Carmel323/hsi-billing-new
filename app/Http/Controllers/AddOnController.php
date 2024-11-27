<?php

namespace App\Http\Controllers;

use App\Models\AccessToken;
use App\Models\AddOn;
use App\Models\HostedPageId;
use App\Models\Subscriptions;
use App\Models\Partner;
use App\Models\PaymentMethod;
use App\Models\Plans;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddOnController extends Controller
{
    public function createAddon(AccessToken $token)
    {
        $app_url = env('APP_URL');

        $addon_url = env('ADDON_URL');

        $id = Route::getCurrentRoute()->id;

        $token = AccessToken::latest('created_at')->first();

        $access_token = $token->access_token;

        $subscription = Subscriptions::where('subscription_id', '=', $id)->first();

        $add_on = AddOn::where('plan_id', '=', $subscription->plan_id)->first();

        $client = new \GuzzleHttp\Client();

        try {
            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                    'content-type: application/json'
                ],
                'json' => [
                    "subscription_id" => $id,
                    "addons" => [
                        "0" => [
                            "addon_code" => $add_on->addon_code,
                            "quantity" => 1,
                        ]
                    ],
                    "redirect_url" => "$app_url/thankyou-update",
                ]
            ];

            $res = $client->request(
                'POST',
                $addon_url,
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

                return back()->with('fail', 'Kindly Subscribe Again!');
            }
        }
    }

    public function getAddon(Request $request)
    {
        $addon = AddOn::where('addon_code', $request->input('addon_code'))->first();
        $plan_id =  $addon->plan_id;
        $subscription =  DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.plan_id')
            ->select('plans.*', 'subscriptions.*')
            ->where('zoho_cust_id', '=', Session::get('loginId'))
            ->first();

        $showModal = false;

        return view('partner.multi-addon-plan', compact('addon', 'subscription', 'showModal'));
    }

    public function selectAddon(Request $request, AccessToken $token)
    {
        $addon_code =  Route::getCurrentRoute()->code;
        $addon = AddOn::where('addon_code', $addon_code)->first();
        $plan = Plans::where('plan_id', $addon->plan_id)->first();
        $subscription = Subscriptions::where('plan_id', $plan->plan_id)->first();

        $app_url = env('APP_URL');

        $addon_url = env('ADDON_URL');

        $token = AccessToken::latest('created_at')->first();

        $access_token = $token->access_token;

        $client = new \GuzzleHttp\Client();

        try {
            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                    'content-type: application/json'
                ],
                'json' => [
                    "subscription_id" => $subscription->subscription_id,
                    "addons" => [
                        "0" => [
                            "addon_code" => $addon->addon_code,
                            "quantity" => 1,
                        ]
                    ],
                    "redirect_url" => "$app_url/thankyou-update",
                ]
            ];

            $res = $client->request(
                'POST',
                $addon_url,
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

                return back()->with('fail', 'Kindly Subscribe Again!');
            }
        }
    }
}
