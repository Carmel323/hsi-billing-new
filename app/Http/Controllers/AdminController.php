<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\Plans;
use App\Models\Subscriptions;
use App\Models\PartnerAddress;
use App\Models\Support;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\AccessToken;
use App\Models\AddOn;
use App\Models\Admin;
use App\Models\Affiliates;
use App\Models\PaymentMethod;
use App\Models\CreditNotes;
use App\Models\enterprise;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Exception\GuzzleException;
use App\Models\HostedPageId;
use Illuminate\Support\Facades\Hash;
use App\Models\Invoices;
use App\Models\Refund;
use App\Models\Terms;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function getPlans()
    {
        if (Session::has('loginAdmin')) {
            $plans = Plans::query()
                ->where('is_cpc', false)
                ->orderBy('price', 'asc')
                ->get();
            $addons = AddOn::all();
            $planCounts = Subscriptions::select(DB::raw('plan_id, count(*) as count'))
                ->groupBy('plan_id')
                ->get()
                ->pluck('count', 'plan_id');
            $addonCounts = Subscriptions::select(DB::raw('plan_id, count(*) as count'))
                ->whereNotNull('addon')  // Add this line to filter by 'addon' column being not null
                ->groupBy('plan_id')
                ->get()
                ->pluck('count', 'plan_id');

            foreach ($plans as $plan) {
                $plan->count = $planCounts[$plan->plan_id] ?? 0;
                $plan->addon_count = $addonCounts[$plan->plan_id] ?? 0;
            }

            return view('admin.plans.flat-plans', compact('plans', 'addons'));
        }
    }

    public function getCPCPlans()
    {
        if (Session::has('loginAdmin')) {
            $plans = Plans::query()
                ->where('is_cpc', true)
                ->orderBy('price', 'asc')
                ->get();
            $addons = AddOn::all();
            $planCounts = Subscriptions::select(DB::raw('plan_id, count(*) as count'))
                ->groupBy('plan_id')
                ->get()
                ->pluck('count', 'plan_id');
            $addonCounts = Subscriptions::select(DB::raw('plan_id, count(*) as count'))
                ->whereNotNull('addon')  // Add this line to filter by 'addon' column being not null
                ->groupBy('plan_id')
                ->get()
                ->pluck('count', 'plan_id');

            foreach ($plans as $plan) {
                $plan->count = $planCounts[$plan->plan_id] ?? 0;
                $plan->addon_count = $addonCounts[$plan->plan_id] ?? 0;
            }

            return view('admin.plans.cpc-plans', compact('plans', 'addons'));
        }
    }



    public function getSubscriptions(Request $request)
    {
        if (!Session::has('loginAdmin')) {
            return redirect('/login')->with('fail', 'Please log in first.');
        }

        try {
            $query = DB::table('subscriptions');

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if ($startDate && $endDate) {
                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }

            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('subscription_number', 'LIKE', "%{$search}%")
                        ->orWhere('status', 'LIKE', "%{$search}%");
                });
            }

            $perPage = $request->input('per_page', 10);

            $subscriptions = $query->orderByDesc('subscription_number')->paginate($perPage);

            $subscriptionsArray = $subscriptions->items();

            $planIds = array_unique(array_column($subscriptionsArray, 'plan_id'));
            $zohoCustIds = array_unique(array_column($subscriptionsArray, 'zoho_cust_id'));

            $plans = DB::table('plans')->whereIn('plan_id', $planIds)->get()->keyBy('plan_id');

            $partners = DB::table('partners')->whereIn('zoho_cust_id', $zohoCustIds)->get()->keyBy('zoho_cust_id');



            foreach ($subscriptionsArray as $subscription) {
                $subscription->plan = $plans->get($subscription->plan_id);
                $subscription->partner = $partners->get($subscription->zoho_cust_id);
            }

            $plans = DB::table('plans')->orderBy('price', 'asc')->get();

            return view('admin.subscription', compact('subscriptions', 'plans'));
        } catch (Exception $e) {
            return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
        }
    }



    public function getProfile(Request $request)
    {
        if (Session::has('loginAdmin')) {
            $admin = Admin::where('id', Session::get('loginId'))->first();
            return view('admin.profile', compact('admin'));
        }
    }

    public function getInvoices(Request $request)
    {
        if (Session::has('loginAdmin')) {
            try {

                $query = Invoices::where('status', 'paid');

                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {

                    $query->whereDate('invoices.created_at', '>=', $startDate)
                        ->whereDate('invoices.created_at', '<=', $endDate);
                }

                if ($request->has('search')) {

                    $search = $request->input('search');

                    $query->where(function ($q) use ($search) {

                        $q->where('invoice_number', 'LIKE', "%{$search}%")
                            ->orWhereJsonContains('invoice_items->name', $search)
                            ->orWhereJsonContains('invoice_items->price', $search)
                            ->orWhere('zoho_cust_id', 'LIKE', "%{$search}%");
                    });
                }

                $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

                $invoices = $query->orderByDesc('invoice_number')->paginate($perPage);


                $invoicesArray = $invoices->items();

                $zohoCustIds = array_unique(array_column($invoicesArray, 'zoho_cust_id'));

                $partners = DB::table('partners')->whereIn('zoho_cust_id', $zohoCustIds)->get()->keyBy('zoho_cust_id');

                foreach ($invoicesArray as $invoice) {

                    $invoice->partner = $partners->get($invoice->zoho_cust_id);
                }


                return view('admin.invoices.paid-invoices', compact('invoices'));
            } catch (Exception $e) {

                return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
            }
        }
    }

    public function getUnpaidInvoices(Request $request)
    {
        if (Session::has('loginAdmin')) {
            try {

                $query = Invoices::where('status', '!=', 'paid');

                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {

                    $query->whereDate('invoices.created_at', '>=', $startDate)
                        ->whereDate('invoices.created_at', '<=', $endDate);
                }

                if ($request->has('search')) {

                    $search = $request->input('search');

                    $query->where(function ($q) use ($search) {

                        $q->where('invoice_number', 'LIKE', "%{$search}%")
                            ->orWhereJsonContains('invoice_items->name', $search)
                            ->orWhereJsonContains('invoice_items->price', $search)
                            ->orWhere('zoho_cust_id', 'LIKE', "%{$search}%");
                    });
                }

                $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

                $invoices = $query->orderByDesc('invoice_number')->paginate($perPage);


                $invoicesArray = $invoices->items();

                $zohoCustIds = array_unique(array_column($invoicesArray, 'zoho_cust_id'));

                $partners = DB::table('partners')->whereIn('zoho_cust_id', $zohoCustIds)->get()->keyBy('zoho_cust_id');

                foreach ($invoicesArray as $invoice) {

                    $invoice->partner = $partners->get($invoice->zoho_cust_id);
                }


                return view('admin.invoices.unpaid-invoices', compact('invoices'));
            } catch (Exception $e) {

                return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
            }
        }
    }

    public function getPartner(Request $request)
    {
        if (Session::has('loginAdmin')) {
            try {

                $query = Partner::query();

                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {

                    $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                }

                if ($request->has('search')) {

                    $search = $request->input('search');

                    $query->where(function ($q) use ($search) {

                        $q->where('company_name', 'LIKE', "%{$search}%");
                    });
                }

                $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

                $partners = $query->orderByDesc('id')->paginate($perPage);

                $affiliates = Affiliates::all();


                return view('admin.partner', compact('partners', 'affiliates'));
            } catch (Exception $e) {

                return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
            }
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'new_password' =>   ['required', Password::min(6)->letters()->numbers()->symbols()],
        ]);
        $current_password = $request->current_password;
        $new_password = $request->new_password;
        $confirm_new_password = $request->confirm_new_password;
        $admin = Admin::where('id', Session::get('loginId'))->first();
        if (Hash::check($current_password, $admin->password)) {
            if ($new_password === $confirm_new_password) {
                $admin->password = Hash::make($new_password);
                $admin->save();
                return back()->with('success', 'Password updated successfully');
            } else {
                return back()->with('fail', 'Password confirmation do not match');
            }
        } else {
            return back()->with('fail', 'Please enter the correct current password');
        }
    }

    public function getInvitePartner()
    {
        $affiliates = Affiliates::all();
        $values = [];
        foreach ($affiliates as $affiliate) {
            array_push($values, $affiliate->isp_affiliate_id . "(" . $affiliate->domain_name . ")");
        }
        $plans = Plans::where('price', '!=', 0)->get();
        $plans = $plans->sortBy('price')->values();
        $lead = null;
        return view('admin/invite-partner', compact('affiliates', 'plans', 'lead', 'values'));
    }


    public function getSupport(Request $request)
    {
        if (Session::has('loginAdmin')) {
            try {
                $query = DB::table('supports');

                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {
                    $query->whereDate('supports.created_at', '>=', $startDate)
                        ->whereDate('supports.created_at', '<=', $endDate);
                }

                if ($request->has('search')) {
                    $search = $request->input('search');
                    $query->where(function ($q) use ($search) {
                        $q->where('request_type', 'LIKE', "%{$search}%")
                            ->orWhere('subscription_number', 'LIKE', "%{$search}%")
                            ->orWhere('message', 'LIKE', "%{$search}%")
                            ->orWhere('status', 'LIKE', "%{$search}%")
                            ->orWhere('comments', 'LIKE', "%{$search}%");
                    });
                }

                $perPage = $request->has('per_page') ? $request->input('per_page') : 10;
                $supports = $query->orderByDesc('supports.created_at')->paginate($perPage);
                $supportsArray = $supports->items();

                $zohoCustIds = array_unique(array_column($supportsArray, 'zoho_cust_id'));

                $partners = DB::table('partners')->whereIn('zoho_cust_id', $zohoCustIds)->get()->keyBy('zoho_cust_id');

                foreach ($supportsArray as $support) {
                    if (isset($partners[$support->zoho_cust_id])) {
                        $support->partner = $partners[$support->zoho_cust_id];
                    } else {
                        $support->partner = null; // Or handle appropriately if partner not found
                    }
                }

                return view('admin.support', compact('supports'));
            } catch (Exception $e) {
                return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
            }
        }
    }

    public function getTerms(Request $request)
    {
        if (Session::has('loginAdmin')) {
            try {
                $query = DB::table('terms');

                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {
                    $query->whereDate('terms.created_at', '>=', $startDate)
                        ->whereDate('terms.created_at', '<=', $endDate);
                }

                if ($request->has('search')) {
                    $search = $request->input('search');
                    $query->where(function ($q) use ($search) {
                        $q->where('subscription_number', 'LIKE', "%{$search}%")
                            ->orWhere('consent', 'LIKE', "%{$search}%")
                            ->orWhere('plan_name', 'LIKE', "%{$search}%")
                            ->orWhere('amount', 'LIKE', "%{$search}%")
                            ->orWhere('browser_agent', 'LIKE', "%{$search}%")
                            ->orWhere('zoho_cust_id', 'LIKE', "%{$search}%")
                            ->orWhere('ip_address', 'LIKE', "%{$search}%");
                    });
                }

                $perPage = $request->has('per_page') ? $request->input('per_page') : 10;
                $terms = $query->orderByDesc('terms.id')->paginate($perPage);
                $termsArray = $terms->items();

                $zohoCustIds = array_unique(array_column($termsArray, 'zoho_cust_id'));

                $partners = DB::table('partners')->whereIn('zoho_cust_id', $zohoCustIds)->get()->keyBy('zoho_cust_id');

                foreach ($termsArray as $term) {

                    if (isset($partners[$term->zoho_cust_id])) {

                        $term->partner = $partners[$term->zoho_cust_id];
                    } else {
                        $term->partner = null;
                    }
                }

                return view('admin.terms', compact('terms'));
            } catch (Exception $e) {
                return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
            }
        }
    }

    public function getClicksEmailLog(Request $request)
    {
        if (Session::has('loginAdmin')) {
            try {
                $query = DB::table('clicks_email_log');

                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {
                    $query->whereDate('clicks_email_log.created_at', '>=', $startDate)
                        ->whereDate('clicks_email_log.created_at', '<=', $endDate);
                }

                if ($request->has('search')) {
                    $search = $request->input('search');
                    $query->where(function ($q) use ($search) {
                        $q->where('details', 'LIKE', "%{$search}%")
                            ->orWhere('clicks_month', 'LIKE', "%{$search}%")
                            ->orWhere('clicks_year', 'LIKE', "%{$search}%")
                            ->orWhere('timestamp', 'LIKE', "%{$search}%")
                            ->orWhere('partner_id ', 'LIKE', "%{$search}%");
                    });
                }

                $perPage = $request->has('per_page') ? $request->input('per_page') : 10;
                $clicks_email_log = $query->orderByDesc('clicks_email_log.id')->paginate($perPage);


                return view('admin.clicks-email-log', compact('clicks_email_log'));
            } catch (Exception $e) {
                return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
            }
        }
    }

    public function getSubscriptionsOld(Request $request)
    {
        if (Session::has('loginAdmin')) {
            try {

                $query = DB::table('subscriptions');

                $startDate = $request->input('start_date');

                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {

                    $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                }

                if ($request->has('search')) {

                    $search = $request->input('search');

                    $query->where(function ($q) use ($search) {

                        $q->where('subscription_number', 'LIKE', "%{$search}%")
                            ->orWhere('status', 'LIKE', "%{$search}%");
                    });
                }

                $perPage = $request->input('per_page', 10);

                $subscriptions = $query->orderByDesc('subscription_number')->paginate($perPage);

                $subscriptionsArray = $subscriptions->items();

                foreach ($subscriptionsArray as $subscription) {


                    $subscription->plan = DB::table('plans')->where('plan_id', $subscription->plan_id)->first();

                    $subscription->partner = DB::table('partners')->where('zoho_cust_id', $subscription->zoho_cust_id)->first();
                }

                $plans = DB::table('plans')->orderBy('price', 'asc')->get();

                return view('admin.subscription', compact('subscriptions', 'plans'));
            } catch (Exception $e) {

                return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
            }
        } else {

            return redirect('/login')->with('fail', 'Please log in first.');
        }
    }

    public function getSupportOld(Request $request)
    {
        if (Session::has('loginAdmin')) {
            try {

                $query = DB::table('supports');

                $startDate = $request->input('start_date');

                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {

                    $query->whereDate('supports.created_at', '>=', $startDate)
                        ->whereDate('supports.created_at', '<=', $endDate);
                }

                if ($request->has('search')) {

                    $search = $request->input('search');

                    $query->where(function ($q) use ($search) {

                        $q->where('request_type', 'LIKE', "%{$search}%")
                            ->orWhere('subscription_number', 'LIKE', "%{$search}%")
                            ->orWhere('message', 'LIKE', "%{$search}%")
                            ->orWhere('status', 'LIKE', "%{$search}%")
                            ->orWhere('comments', 'LIKE', "%{$search}%");
                    });
                }

                $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

                $supports = $query->orderByDesc('supports.created_at')->paginate($perPage);

                $supportsArray = $supports->items();

                foreach ($supportsArray as $support) {
                    $support->partner = DB::table('partners')->where('zoho_cust_id', $support->zoho_cust_id)->first();
                }


                return view('admin.support', compact('supports'));
            } catch (Exception $e) {

                return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
            }
        }
    }

    public function getTermsOld(Request $request)
    {
        if (Session::has('loginAdmin')) {
            try {

                $query = DB::table('terms');

                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {

                    $query->whereDate('terms.created_at', '>=', $startDate)
                        ->whereDate('terms.created_at', '<=', $endDate);
                }

                if ($request->has('search')) {

                    $search = $request->input('search');

                    $query->where(function ($q) use ($search) {

                        $q->where('subscription_number', 'LIKE', "%{$search}%")
                            ->orWhere('consent', 'LIKE', "%{$search}%")
                            ->orWhere('plan_name', 'LIKE', "%{$search}%")
                            ->orWhere('amount', 'LIKE', "%{$search}%")
                            ->orWhere('browser_agent', 'LIKE', "%{$search}%")
                            ->orWhere('zoho_cust_id', 'LIKE', "%{$search}%")
                            ->orWhere('ip_address', 'LIKE', "%{$search}%");
                    });
                }

                $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

                $terms = $query->orderByDesc('terms.id')->paginate($perPage);

                $termsArray = $terms->items();

                foreach ($termsArray as $term) {
                    $term->partner = DB::table('partners')->where('zoho_cust_id', $term->zoho_cust_id)->first();
                }

                return view('admin.terms', compact('terms'));
            } catch (Exception $e) {

                return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
            }
        }
    }

    public function updateSupport(Request $request, AccessToken $token, Subscriptions $subscriptions)
    {
        $app_url = env('APP_URL');
        $token1 = AccessToken::latest('created_at')->first();
        $support = Support::where('id', $request->id)->first();
        if ($token1 !== null) {
            $access_token = $token1->access_token;
        } else {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
            return back()->with('fail', 'Kindly Try Again');
        }
        $update_url = env('UPDATE_SUBSCRIPTION_URL');
        if ($support->request_type === "Downgrade") {
            $subscription = Subscriptions::where('subscription_number', '=', $support->subscription_number)->first();
            $next_plan = $support->attributes['next_plan'];
            $plan = Plans::where('plan_name', '=', $next_plan)->first();


            $plan = Plans::where('plan_id', '=', $plan->plan_id)->first();

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
                        "redirect_url" =>  "$app_url/thankyou-downgrade",
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

                if ($response->message === "You are not authorized to perform this operation") {

                    $token->getToken();

                    return redirect('/admin/support')->with('fail', 'Kindly Subscribe Again!');
                }
            }
        } else if ($support->request_type === "Cancellation") {

            $subscription = Subscriptions::where('subscription_number', '=', $support->subscription_number)->first();

            $paymentMethod = PaymentMethod::where('zoho_cust_id', $subscription->zoho_cust_id)->first();

            $token1 = AccessToken::latest('created_at')->first();

            if ($token1 !== null) {

                $access_token = $token1->access_token;
            } else {

                $token->getToken();

                $token1 = AccessToken::latest('created_at')->first();

                return back()->with('fail', 'Kindly Try Again');
            }

            $free_plan = Plans::where('price', '0')->first();

            $subscriptions->dowgradeToFreePlan($access_token, $paymentMethod, $free_plan, $subscription);

            return redirect('/cancel-subscription/' . $support->zoho_cust_id);
        } else if ($support->request_type === "Custom Enterprise") {

            $support->status = "Completed";
            $support->comments = "Unable to revoke";
            $support->save();

            return redirect('/admin/support');
        } else if ($support->request_type === "Custom Support") {
            $support->status = "Completed";
            $support->comments = "Unable to revoke";
            $support->save();
            return redirect('/admin/support');
        } else if ($support->request_type === "Address") {

            $partner_url = env('PARTNER_URL');

            $partner = Partner::where('zoho_cust_id', $support->zoho_cust_id)->first();

            $client = new \GuzzleHttp\Client();

            try {
                $options = [
                    'headers' => [
                        'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                        'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                        'content-type: application/json'
                    ],
                    'json' => [
                        "billing_address" => [
                            "attention" => $partner->first_name . " " . $partner->last_name,
                            "street" => $support->attributes['address'],
                            "city" => $support->attributes['city'],
                            "state" => $support->attributes['state'],
                            "zip" => $support->attributes['zip_code'],
                            "country" => $support->attributes['country'],
                        ],
                        "shipping_address" => [
                            "attention" =>  $partner->first_name . " " . $partner->last_name,
                            "street" => $support->attributes['address'],
                            "city" => $support->attributes['city'],
                            "state" =>  $support->attributes['state'],
                            "zip" => $support->attributes['zip_code'],
                            "country" => $support->attributes['country'],
                        ],
                    ],

                ];


                $res = $client->request(
                    'PUT',
                    $partner_url . '/' . $partner->zoho_cust_id,
                    $options
                );

                $response = (string) $res->getBody();
                $response = json_decode($response);
                $address = $response->customer->billing_address;

                $partner_address = PartnerAddress::where('zoho_cust_id', $support->zoho_cust_id)->first();

                $partner_address->street = $address->street;
                $partner_address->city = $address->city;
                $partner_address->state = $address->state;
                $partner_address->country = $address->country;
                $partner_address->zip_code = $address->zip;
                $partner_address->save();

                $support->status = "Completed";
                $support->comments = "Unable to revoke";
                $support->save();

                return redirect('/admin/support');
            } catch (GuzzleException $e) {

                $response = $e->getResponse()->getBody(true);
                $response = json_decode($response);

                if ($response->message === "You are not authorized to perform this operation") {

                    $token->getToken();

                    return redirect('/admin/support')->with('fail', 'Kindly Subscribe Again!');
                }
            }
        } else if ($support->request_type === "Refund") {
            $refund_url = env('REFUND_URL');
            $token1 = AccessToken::latest('created_at')->first();
            if ($token1 !== null) {
                $access_token = $token1->access_token;
            } else {
                $token->getToken();
                $token1 = AccessToken::latest('created_at')->first();
            }


            $client = new \GuzzleHttp\Client();

            try {
                $options = [
                    'headers' => [
                        'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                        'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                        'content-type: application/json'
                    ],
                    'json' => [
                        "amount" => $support->attributes['refund_amount'],
                        "description" => $support->message,
                    ],

                ];


                $res = $client->request(
                    'POST',
                    $refund_url . $request->id,
                    $options
                );

                $response = (string) $res->getBody();
                $response = json_decode($response);
                $refund = $response->refund;

                $refund_add = new Refund();
                $refund_add->refund_id = $refund->refund_id;
                $refund_add->creditnote_id = $refund->creditnote->creditnote_id;
                $refund_add->balance_amount = $refund->creditnote->balance_amount;
                $refund_add->refund_amount = $refund->creditnote->refund_amount;
                $refund_add->description = $refund->description;
                $refund_add->zoho_cust_id = $refund->zoho_cust_id;
                $refund_add->creditnote_number = $refund->creditnote->creditnote_number;
                $refund_add->save();

                $creditnote = CreditNotes::where('creditnote_id', '=', $refund->creditnote->creditnote_id)->first;
                $creditnote->balance = $refund->creditnote->balance_amount;
                $creditnote->save();

                return redirect('/creditnotes')->with('success', 'Refunded successfully');
            } catch (GuzzleException $e) {

                $response = $e->getResponse()->getBody(true);
                $response = json_decode($response);

                if ($response->message === "You are not authorized to perform this operation") {

                    $token->getToken();

                    return redirect('/')->with('fail', 'Kindly Subscribe Again!');
                }
            }
        }
    }
}
