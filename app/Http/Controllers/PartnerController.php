<?php

namespace App\Http\Controllers;

use App\Mail\AssociatePaymentMethod;
use App\Mail\CreateSubscription;
use App\Mail\Email;
use Illuminate\Http\Request;
use App\Models\AccessToken;
use App\Models\Admin;
use App\Models\AffiliateId;
use App\Models\Affiliates;
use App\Models\BudgetCapSettings;
use App\Models\Clicks;
use App\Models\CreditNotes;
use App\Models\Partner;
use App\Models\Invoices;
use App\Models\Leads;
use App\Models\Support;
use App\Models\PartnerAddress;
use App\Models\PartnersAffiliates;
use App\Models\PartnerUsers;
use App\Models\PaymentMethod;
use App\Models\Plans;
use App\Models\ProviderAvailabilityData;
use App\Models\ProviderData;
use App\Models\Refund;
use App\Models\SelectedPlan;
use App\Models\Subscriptions;
use Carbon\Carbon;
use PDF;
use App\Models\Users;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use SplTempFileObject;

class PartnerController extends Controller
{
    public function invitePartner(Request $request, AccessToken $token, Support $support)
    {

        $request->validate([
            'company_name' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string',
            'phone_number' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required|string',
            'affiliate_ids.*' => 'required|string',
        ]);
        try {

            $partner_url = env('PARTNER_URL');

            $access_token = $this->getAccessToken();

            if (!$access_token) {

                return back()->with('fail', 'Kindly Try Again');
            }

            $existingPartner = $this->findPartnerwithEmail($request->email, $request);


            if ($this->partnerExists($request->email)) {

                return back()->with('fail', 'Email already exists');
            }



            $response = $this->createPartnerInZoho($request, $access_token, $partner_url);

            if (!$response) {

                return back()->with('fail', 'Failed to create partner in Zoho');
            }

            $zoho_cust = $response->customer;

            $savedPartner = $this->savePartner($zoho_cust, $request);

            $partner = $savedPartner['partner'];

            $unhashedPassword = $savedPartner['unhashed_password'];

            if (!$partner) {

                return back()->with('fail', 'Failed to save partner data');
            }

            return redirect('/admin/partner')->with('success', 'Email sent successfully');
        } catch (GuzzleException $e) {

            if ($this->handleGuzzleException($e, $token)) {

                return redirect('/admin/partner')->with('fail', 'Kindly Invite Again');
            }
        }
    }



    private function partnerExists($email)
    {
        return PartnerUsers::where('email', $email)->exists();
    }

    private function getAccessToken()
    {

        $latestToken = AccessToken::latest('created_at')->first();

        return $latestToken ? $latestToken->access_token : null;
    }

    private function createPartnerInZoho($request, $access_token, $partner_url)
    {
        $client = new \GuzzleHttp\Client();

        try {
            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                    'content-type: application/json'
                ],
                'json' => [
                    "display_name" => $request->company_name,
                    "first_name" => $request->first_name,
                    "last_name" => $request->last_name,
                    "email" => $request->email,
                    "can_add_bank_account" => $request->payment_method === "bank_account" ? true : false,
                    "ach_supported" => $request->payment_method === "bank_account" ? true : false,
                    "can_add_card" => $request->payment_method === "card" ? true : false,
                    "company_name" => $request->company_name,
                    "billing_address" => [
                        "attention" => $request->first_name . " " . $request->last_name,
                        "street" => $request->address,
                        "city" => $request->city,
                        "state" => $request->state,
                        "zip" => $request->zip_code,
                        "country" => $request->country
                    ],
                    "shipping_address" => [
                        "attention" =>  $request->first_name . " " . $request->last_name,
                        "street" => $request->address,
                        "city" => $request->city,
                        "state" =>  $request->state,
                        "zip" => $request->zip_code,
                        "country" => $request->country
                    ],
                    "payment_terms" => 0,
                    "ach_supported" => true,
                    "payment_terms_label" => "Due on receipt",
                    "currency_code" => "USD",
                    "custom_fields" => [
                        [
                            "label" => "isp_affiliate_id",
                            "value" => $request->affiliate_id,
                        ],
                        [
                            "label" => "isp_advertiser_id",
                            "value" => null,
                        ],
                        [
                            "label" => "isp_tax_number",
                            "value" => $request->tax_number
                        ]
                    ],
                ]
            ];

            $res = $client->request(
                'POST',
                $partner_url,
                $options
            );


            $response = (string) $res->getBody();

            return json_decode($response);
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            \Log::error('Guzzle HTTP Exception: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {

            \Log::error('Exception: ' . $e->getMessage());
            return null;
        }
    }


    function findPartnerwithEmail($email_id, $request)
    {

        if ($this->partnerExists($request->email)) {

            return back()->with('fail', 'Email already exists');
        }


        $access_token = $this->getAccessToken();

        $url = env('PARTNER_URL') . '?email_equal=' . $email_id;

        $client = new \GuzzleHttp\Client();

        try {

            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                    'content-type: application/json'
                ]
            ]);

            $body = $response->getBody()->getContents();

            $data = json_decode($body, true);

            if (isset($data['partners']) && !empty($data['partners'])) {

                foreach ($data['partners'] as $partner) {

                    if ($partner['email'] == $email_id) {

                        $this->savePartner((object) $partner, $request);
                    }
                }
            }

            return null;
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            return null;
        }
    }
    public function invitePartnerAgain(Request $request)
    {

        $partner = Partner::where('id', $request->id)->first();

        $partner_user = PartnerUsers::where('zoho_cpid', $request->zoho_cpid)->first();

        if (!$partner) {

            return redirect()->back()->with('error', 'Partner not found');
        }
        $unhashedPassword = Str::random(16);

        $company_name = $partner->company_name;

        $partner_user->password = Hash::make($unhashedPassword);

        $partner_user->save();

        $emailSent = $this->sendEmail($partner_user->first_name, $partner_user->email, $unhashedPassword, $company_name);

        if (!$emailSent) {

            return redirect('/admin/partner')->with('error', 'Failed to send email');
        }

        return redirect('/admin/view-partner/' . $partner->id)->with('success', 'Invitation Email sent successfully');
    }



    private function savePartner($zoho_partner, $request)
    {

        $unhashedPassword = Str::random(16);
        $partner = new Partner();
        $partner->zoho_cust_id = $zoho_partner->customer_id;
        $partner->company_name = $zoho_partner->company_name;
        $partner->isp_advertiser_id = $request->advertiser_id;
        $partner->tax_number = $request->tax_number;
        $partner->payment_gateway = $request->payment_gateway;
        $partner->status = "Invited";
        $partner->save();

        $affiliateIds = $request->affiliate_ids[0];

        $pattern = '/(\d+)\(/';;

        preg_match_all($pattern, $affiliateIds, $matches);

        $affiliateIdsArray = $matches[1];

        $affiliateIdsFromTable = Affiliates::whereIn('isp_affiliate_id', $affiliateIdsArray)
            ->pluck('id');

        foreach ($affiliateIdsFromTable as $affiliateId) {
            $partner_affiliate = new PartnersAffiliates();
            $partner_affiliate->affiliate_id = $affiliateId;
            $partner_affiliate->partner_id = $partner->id;
            $partner_affiliate->save();
        }

        $partnerUser = new PartnerUsers();
        $partnerUser->zoho_cust_id = $zoho_partner->customer_id;
        $partnerUser->zoho_cpid = $zoho_partner->primary_contactperson_id;
        $partnerUser->first_name = $zoho_partner->first_name;
        $partnerUser->last_name = $zoho_partner->last_name;
        $partnerUser->email = $zoho_partner->email;
        $partnerUser->phone_number = $request->phone_number;
        $partnerUser->status = 'active';
        $partnerUser->is_primary = true;
        $partnerUser->password = Hash::make($unhashedPassword);
        $partnerUser->invitation_status = "Invited";
        $partnerUser->save();

        if ($request->lead_id) {
            $lead = Leads::find($request->lead_id);
            $lead->status = "Approved";
            $lead->save();
        }

        $emailSent = $this->sendEmail($request->first_name, $request->email, $unhashedPassword, $partner->company_name);

        $this->selectPlans($partner, $request);

        if (!$emailSent) {

            return back()->with('fail', 'Failed to send invitation email');
        }

        if (!empty($zoho_partner->billing_address)) {

            $this->savePartnerAddress($zoho_partner, $partner->zoho_cust_id);
        }

        return [
            'partner' => $partner,
            'unhashed_password' => $unhashedPassword
        ];
    }


    private function selectPlans($partner, $request)
    {
        $selectedOptions = $request->options;
        $jsonData = json_encode($selectedOptions);
        $partner->selected_plans =  $jsonData;
        $partner->save();
    }



    private function savePartnerAddress($partners, $partnerId)
    {
        $partner_address = new PartnerAddress();
        $partner_address->street = $partners->billing_address->street;
        $partner_address->state = $partners->billing_address->state;
        $partner_address->city = $partners->billing_address->city;
        $partner_address->country = $partners->billing_address->country;
        $partner_address->zip_code = $partners->billing_address->zip;
        $partner_address->zoho_cust_id = $partnerId;
        $partner_address->save();
    }


    public function sendEmail($name, $email, $password, $company_name)
    {
        $app_url = env('APP_URL');

        try {
            Mail::to($email)->send(new Email($app_url, $name, $password, $company_name));
            return "Mail send!";
        } catch (\Exception $e) {

            Log::error('Failed to send email to ' . $email . '. Error: ' . $e->getMessage());

            return "Failed to send email";
        }
    }

    public function show($id)
    {

        $invoice = Invoices::where('zoho_cust_id', '=', $id)->get();
        return response()->json($invoice);
    }

    public function viewPartnerOverview()
    {
        $id = Route::getCurrentRoute()->id;

        $partner = Partner::where('id', $id)->first();

        $partner_address = PartnerAddress::where('zoho_cust_id', $partner->zoho_cust_id)->first();

        $paymentmethod = PaymentMethod::where('zoho_cust_id', $partner->zoho_cust_id)->first();

        $subscription = Subscriptions::where('zoho_cust_id', $partner->zoho_cust_id)->where('status', 'live')->first();

        $primary_user = PartnerUsers::where('zoho_cust_id', $partner->zoho_cust_id)->where('is_primary', true)->first();

        $users = PartnerUsers::where('zoho_cust_id', $partner->zoho_cust_id)->get();

        $affiliates = PartnersAffiliates::where('partner_id', $id)->get();

        $affiliate_id = $affiliates->pluck('affiliate_id')->toArray();

        $isp_affiliates = Affiliates::whereIn('id', $affiliate_id)->get();

        $remaining_affiliates = Affiliates::whereNotIn('id', $affiliate_id)
            ->get(['*']);
        $partner_plans = $partner->selected_plans ?? null;
        $selected_partner_plans = json_decode($partner_plans);
        $selected_plans = SelectedPlan::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        if ($selected_plans) {
            $selected_plan_id = $selected_plans->plan_id;
            $plans = Plans::where('price', '!=', '0')
                ->whereIn('plans.plan_id', $selected_partner_plans)
                ->where('plan_id', '!=', $selected_plan_id)->get();
        } else {
            $selected_plan_id = null;
            $plans = Plans::where('price', '!=', '0')->get();
        }

        $selected_plan = Plans::where('plan_id', $selected_plan_id)->first();
        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $company_info = ProviderData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $payment_gateway = $partner->payment_gateway;
        $budget_cap = BudgetCapSettings::where('partner_id', $partner->id)->first();

        return view('admin/view/overview', compact('partner', 'partner_address', 'budget_cap', 'paymentmethod', 'payment_gateway', 'subscription', 'users', 'primary_user', 'isp_affiliates', 'remaining_affiliates', 'company_info', 'availability_data', 'selected_plan', 'plans'));
    }

    public function viewPartnerSubscriptions()
    {
        $id = Route::getCurrentRoute()->id;
        $partner = Partner::where('id', $id)->first();
        $partner_plans = $partner->selected_plans ?? null;
        $selected_partner_plans = json_decode($partner_plans);
        $plans_for_update = null;
        $subscriptions = DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.plan_id')
            ->select('plans.*', 'subscriptions.*')
            ->where('zoho_cust_id', '=', $partner->zoho_cust_id)
            ->get();

        $subscription = Subscriptions::where('zoho_cust_id', '=', $partner->zoho_cust_id)->first();

        if ($subscription) {
            $plans_for_update = Plans::where('plan_id', '!=', $subscription->plan_id)->whereIn('plans.plan_id', $selected_partner_plans)->where('price', '!=', '0')->get();
        }


        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $company_info = ProviderData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $showModal = false;

        if ($availability_data === null || $company_info === null) {
            $showModal = true;
        }

        $selected_plans = SelectedPlan::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        if ($selected_plans) {
            $selected_plan_id = $selected_plans->plan_id;
            $plans = Plans::where('price', '!=', '0')
                ->whereIn('plans.plan_id', $selected_partner_plans)
                ->where('plan_id', '!=', $selected_plan_id)->get();
        } else {
            $selected_plan_id = null;
            $plans = Plans::where('price', '!=', '0')->get();
        }
        $selected_plan = Plans::where('plan_id', $selected_plan_id)->first();

        $paymentmethod = PaymentMethod::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $budget_cap = BudgetCapSettings::where('partner_id', $partner->id)->first();

        return view('admin/view/subscriptions', compact('partner', 'subscriptions', 'budget_cap', 'plans', 'plans_for_update', 'availability_data', 'company_info', 'showModal', 'selected_plan', 'paymentmethod'));
    }

    public function viewPartnerCreditNotes(Request $request)
    {
        $id = Route::getCurrentRoute()->id;
        $partner = Partner::where('id', $id)->first();
        $query = DB::table('credit_notes')
            ->where('zoho_cust_id', $partner->zoho_cust_id);


        $totalCount = DB::table('credit_notes')
            ->join('partners', 'credit_notes.zoho_cust_id', '=', 'partners.zoho_cust_id')
            ->select('partners.*', 'credit_notes.*')
            ->where('credit_notes.zoho_cust_id', $partner->zoho_cust_id)->count();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {

            $query->whereDate('credit_notes.created_at', '>=', $startDate)
                ->whereDate('credit_notes.created_at', '<=', $endDate);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('creditnote_number', 'LIKE', "%{$search}%")
                    ->orWhere('credited_amount', 'LIKE', "%{$search}%")
                    ->orWhere('balance', 'LIKE', "%{$search}%");
            });
        }

        $query->orderByDesc('creditnote_number'); // Order by creditnote_number in descending order

        $perPage = $request->input('per_page', 10);

        $creditnotes = $query->paginate($perPage);

        $creditnotesArray = $creditnotes->items();

        $zohoCustIds = array_unique(array_column($creditnotesArray, 'zoho_cust_id'));

        $partners = DB::table('partners')->whereIn('zoho_cust_id', $zohoCustIds)->get()->keyBy('zoho_cust_id');

        foreach ($creditnotesArray as $creditnote) {

            if (isset($partners[$creditnote->zoho_cust_id])) {

                $creditnote->partner = $partners[$creditnote->zoho_cust_id];
            } else {

                $creditnote->partner = null;
            }
        }

        $subscription =  Subscriptions::where('zoho_cust_id', $partner->zoho_cust_id)->first();

        if ($subscription) {
            $plan = Plans::where('plan_id', $subscription->plan_id)->first();
        } else {
            $plan = null;
        }

        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $company_info = ProviderData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $partner_plans = $partner->selected_plans ?? null;
        $selected_partner_plans = json_decode($partner_plans);
        $selected_plans = SelectedPlan::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        if ($selected_plans) {
            $selected_plan_id = $selected_plans->plan_id;
            $plans = Plans::where('price', '!=', '0')
                ->whereIn('plans.plan_id', $selected_partner_plans)
                ->where('plan_id', '!=', $selected_plan_id)->get();
        } else {
            $selected_plan_id = null;
            $plans = Plans::where('price', '!=', '0')->get();
        }
        $selected_plan = Plans::where('plan_id', $selected_plan_id)->first();

        $paymentmethod = PaymentMethod::where('zoho_cust_id', $partner->zoho_cust_id)->first();

        $budget_cap = BudgetCapSettings::where('partner_id', $partner->id)->first();

        return view('admin/view/creditnotes', compact('partner', 'creditnotes', 'budget_cap', 'totalCount', 'paymentmethod', 'plan', 'availability_data', 'company_info', 'plans', 'selected_plan'));
    }


    public function viewPartnerInvoices(Request $request)
    {
        $id = Route::getCurrentRoute()->id;

        $partner = Partner::where('id', $id)->first();

        $query = Invoices::where('zoho_cust_id', $partner->zoho_cust_id);

        $totalCount = Invoices::where('zoho_cust_id', $partner->zoho_cust_id)->count();

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
                    ->orWhereJsonContains('payment_details->payment_id', $search);
            });
        }

        $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

        $invoices = $query->orderByDesc('invoice_number')->paginate($perPage);

        $refundedPaymentIds = Refund::pluck('parent_payment_id')->toArray();

        $invoices_for_update = Invoices::where('zoho_cust_id', $partner->zoho_cust_id)
            ->whereNotIn('payment_details->payment_id', $refundedPaymentIds)
            ->orderByDesc('invoice_number')
            ->get();

        $subscription = Subscriptions::where('zoho_cust_id', $partner->zoho_cust_id)->first();

        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $company_info = ProviderData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $partner_plans = $partner->selected_plans ?? null;
        $selected_partner_plans = json_decode($partner_plans);
        $selected_plans = SelectedPlan::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        if ($selected_plans) {
            $selected_plan_id = $selected_plans->plan_id;
            $plans = Plans::where('price', '!=', '0')
                ->whereIn('plans.plan_id', $selected_partner_plans)
                ->where('plan_id', '!=', $selected_plan_id)->get();
        } else {
            $selected_plan_id = null;
            $plans = Plans::where('price', '!=', '0')->get();
        }
        $selected_plan = Plans::where('plan_id', $selected_plan_id)->first();
        $paymentmethod = PaymentMethod::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $budget_cap = BudgetCapSettings::where('partner_id', $partner->id)->first();
        return view('admin/view/invoices', compact('partner', 'invoices', 'totalCount', 'budget_cap', 'paymentmethod', 'selected_plan', 'plans', 'subscription', 'invoices_for_update', 'availability_data', 'company_info'));
    }

    public function viewPartnerRefunds(Request $request)
    {
        $id = Route::getCurrentRoute()->id;
        $partner = Partner::where('id', $id)->first();
        $totalCount = Refund::where('zoho_cust_id', $partner->zoho_cust_id)->count();
        $query =  Refund::where('zoho_cust_id', $partner->zoho_cust_id);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {

            $query->whereDate('refunds.created_at', '>=', $startDate)
                ->whereDate('refunds.created_at', '<=', $endDate);
        }

        if ($request->has('search')) {

            $search = $request->input('search');

            $query->where(function ($q) use ($search) {

                $q->where('creditnote_number', 'LIKE', "%{$search}%")
                    ->where('balance_amount', 'LIKE', "%{$search}%")
                    ->where('refund_amount', 'LIKE', "%{$search}%");
            });
        }

        $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

        $refunds = $query->orderByDesc('created_at')->paginate($perPage);

        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $company_info = ProviderData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $partner_plans = $partner->selected_plans ?? null;
        $selected_partner_plans = json_decode($partner_plans);
        $selected_plans = SelectedPlan::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        if ($selected_plans) {
            $selected_plan_id = $selected_plans->plan_id;
            $plans = Plans::where('price', '!=', '0')
                ->whereIn('plans.plan_id', $selected_partner_plans)
                ->where('plan_id', '!=', $selected_plan_id)->get();
        } else {
            $selected_plan_id = null;
            $plans = Plans::where('price', '!=', '0')->get();
        }
        $selected_plan = Plans::where('plan_id', $selected_plan_id)->first();
        $paymentmethod = PaymentMethod::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $budget_cap = BudgetCapSettings::where('partner_id', $partner->id)->first();

        return view('admin/view/refunds', compact('partner', 'refunds', 'totalCount', 'budget_cap', 'paymentmethod', 'availability_data', 'company_info', 'selected_plan', 'plans'));
    }

    public function viewPartnerProviderData(Request $request)
    {
        $id = Route::getCurrentRoute()->id;
        $partner = Partner::where('id', $id)->first();
        $zohoCustId = $partner->zoho_cust_id;
        $data = ProviderData::where('zoho_cust_id', $zohoCustId)->first();

        $url = null;
        if ($data) {
            $url = $this->generatePresignedUrl($data->logo_image);
        }

        $query = ProviderAvailabilityData::where('zoho_cust_id', $zohoCustId);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {

            $query->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        if ($request->has('search')) {

            $search = $request->input('search');

            $query->where(function ($q) use ($search) {

                $q->where('file_name', 'LIKE', "%{$search}%")
                    ->orWhere('zip_count', 'LIKE', "%{$search}%");
            });
        }

        $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

        $availability_data = $query->orderByDesc('created_at')->paginate($perPage);

        $totalCount = DB::table('provider_availability_data')->where('zoho_cust_id', $zohoCustId)->count();

        $company_info = ProviderData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $partner_plans = $partner->selected_plans ?? null;
        $selected_partner_plans = json_decode($partner_plans);
        $selected_plans = SelectedPlan::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        if ($selected_plans) {
            $selected_plan_id = $selected_plans->plan_id;
            $plans = Plans::where('price', '!=', '0')
                ->whereIn('plans.plan_id', $selected_partner_plans)
                ->where('plan_id', '!=', $selected_plan_id)->get();
        } else {
            $selected_plan_id = null;
            $plans = Plans::where('price', '!=', '0')->get();
        }
        $selected_plan = Plans::where('plan_id', $selected_plan_id)->first();
        $admins = Admin::all();
        $paymentmethod = PaymentMethod::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $budget_cap = BudgetCapSettings::where('partner_id', $partner->id)->first();
        return view('admin/view/provider-data', compact('partner', 'data', 'availability_data', 'budget_cap', 'paymentmethod', 'admins', 'totalCount', 'url', 'company_info', 'selected_plan', 'plans'));
    }

    public function generatePresignedUrl($objectKey)
    {
        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION'),
            ]);

            $command = $s3Client->getCommand('GetObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $objectKey,
            ]);

            $presignedUrl = (string) $s3Client->createPresignedRequest($command, '+6 days')->getUri();

            return $presignedUrl;
        } catch (AwsException $e) {
            \Log::error('Error generating presigned URL: ' . $e->getMessage());
            return null;
        }
    }

    public function viewPartnerSelectedPlans(Request $request)
    {
        $id = Route::getCurrentRoute()->id;
        $partner = Partner::where('id', $id)->first();
        $zohoCustId = $partner->zoho_cust_id;
        $partner_plans = $partner->selected_plans ?? null;
        $selected_plans = json_decode($partner_plans);
        $plans1 = Plans::where('price', '!=', 0)->get();
        $plans1 = $plans1->sortBy('price')->values();
        $subscriptionlive = Subscriptions::where('zoho_cust_id', '=',  $zohoCustId)
            ->where('status', '=', 'live')
            ->first();
        if ($subscriptionlive && !empty($subscriptionlive->plan_id)) {

            $current_plan = Plans::where('plan_id', $subscriptionlive->plan_id)->first();
            $is_enterprise_plan = stripos($current_plan->plan_code, 'enterprise') !== false;
        } else {
            $current_plan = null;
            $is_enterprise_plan = null;
        }
        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $company_info = ProviderData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $selected_plans1 = SelectedPlan::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        if ($selected_plans1) {
            $selected_plan_id = $selected_plans1->plan_id;
            $plans = Plans::where('price', '!=', '0')
                ->whereIn('plans.plan_id', $selected_plans)
                ->where('plan_id', '!=', $selected_plan_id)->get();
        } else {
            $selected_plan_id = null;
            $plans = Plans::where('price', '!=', '0')->get();
        }
        $selected_plan = Plans::where('plan_id', $selected_plan_id)->first();
        $paymentmethod = PaymentMethod::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $budget_cap = BudgetCapSettings::where('partner_id', $partner->id)->first();

        return view('admin/view/selected-plan', compact('partner', 'plans1', 'selected_plans', 'budget_cap', 'paymentmethod', 'plans', 'selected_plan', 'current_plan', 'is_enterprise_plan', 'availability_data', 'company_info'));
    }

    public function addSelectedPlans(Request $request)
    {
        $partner = Partner::where('id', $request->partner_id)->first();
        $selectedOptions = $request->options;
        $jsonData = json_encode($selectedOptions);
        $partner->selected_plans =  $jsonData;
        $partner->save();
        return back()->with('success', 'Selected plans added successfully');
    }

    public function noSelectedPlans()
    {
        $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();

        $partner_plans = $partner->selected_plans ?? null;
        $selected_plans = json_decode($partner_plans);
        $showModal = false;

        if ($selected_plans) {
            return redirect('/');
        }
        return view('partner/no-selected-plans', compact('showModal'));
    }
    public function viewPartnerClicksDataNewOld(Request $request)
    {


        $id = Route::getCurrentRoute()->id;
        $partner = Partner::where('id', $id)->first();
        $affiliate_ids = PartnersAffiliates::where('partner_id', $partner->id)->pluck('affiliate_id')->toArray();
        $partner_affiliate_ids = PartnersAffiliates::where('partner_id', $partner->id)->pluck('id')->toArray();
        $affiliates = Affiliates::whereIn('id', $affiliate_ids)->get();

        $query = Clicks::whereIn('partners_affiliates_id', $partner_affiliate_ids);

        $now = Carbon::now();
        $filter = $request->input('filter', 'mtd');
        $dataSplit = $request->input('data_split', 'daily');
        $dateFrom = null;
        $dateTo = null;

        if ($request->has('date_from')) {
            $dateFrom = Carbon::parse($request->get('date_from'));
        }
        if ($request->has('date_to')) {
            $dateTo = Carbon::parse($request->get('date_to'));
        }

        switch ($filter) {
            case 'mtd':
                $dateFrom =  $now->copy()->startOfMonth();
                $dateTo =  $now->copy()->subDay();
                if ($dataSplit === 'monthly') {
                    $dataSplit = 'daily';
                }
                break;
            case 'this_month':
                $dateFrom = $now->copy()->startOfMonth();
                $dateTo = $now->copy();
                if ($dataSplit === 'monthly') {
                    $dataSplit = 'daily';
                }
                break;
            case 'last_12_months':
                $dateFrom = $now->copy()->subMonths(12);
                $dateTo = $now->copy();
                break;
            case 'last_6_months':
                $dateFrom = $now->copy()->subMonths(6);
                $dateTo = $now->copy();
                break;
            case 'last_3_months':
                $dateFrom = $now->copy()->subMonths(3);
                $dateTo = $now->copy();
                break;
            case 'last_1_month':
                $dateFrom =  $now->copy()->subMonth();
                $dateTo = $now->copy();
                break;
            case 'last_7_days':
                $dateFrom = $now->copy()->subDays(7);
                $dateTo = $now->copy();
                break;
            case '-':
                $dateFrom = Carbon::parse($request->get('date_from'));
                $dateTo = Carbon::parse($request->get('date_to'));
                break;
            default:
                $dateFrom = $now->copy()->subMonths(12);
                $dateTo = $now->copy();
                break;
        }

        $query->where('click_ts', '>=', $dateFrom)
            ->where('click_ts', '<=', $dateTo);

        $total_clicks = $query->count();

        $chartData = [];

        foreach ($query as $result) {

            $chartData[] = [
                'partner_affiliate_id' => $result->partner_affiliate_id,
                'affiliate_id' => $result->affiliate_id,
                'domain_name' => $result->domain_name,
                'total_clicks' => $result->total_clicks,
            ];
        }

        $chartData = [];

        if ($dataSplit == 'daily') {

            $results = $query->select(
                DB::raw('DATE(click_ts) as date,COUNT(*) as total_clicks'),
                'partners_affiliates.id as partner_affiliate_id',
                'affiliates.id as affiliate_id',
                'affiliates.domain_name'
            )
                ->join('partners_affiliates', 'partners_affiliates.id', '=', 'partners_affiliates_id')
                ->join('affiliates', 'partners_affiliates.affiliate_id', '=', 'affiliates.id')
                ->groupBy('partners_affiliates.id', 'affiliates.id', 'affiliates.domain_name', DB::raw('DATE(click_ts)'))
                ->orderBy(DB::raw('DATE(click_ts)'), 'asc')
                ->get();


            foreach ($results as $result) {
                $formattedDate = Carbon::parse($result->date)->format('d M Y');


                if (!isset($chartData[$result->domain_name])) {
                    $chartData[$result->domain_name] = [];
                }


                $chartData[$result->domain_name][] = [
                    'total_clicks' => $result->total_clicks,
                    'click_date' => $formattedDate,
                ];
            }
        } elseif ($dataSplit == 'weekly') {
            $results = $query->select(
                DB::raw('YEAR(click_ts) as year, WEEK(click_ts) as week, MIN(click_ts) as from_date, MAX(click_ts) as to_date, COUNT(*) as total_clicks'),
                'partners_affiliates.id as partner_affiliate_id',
                'affiliates.id as affiliate_id',
                'affiliates.domain_name'
            )
                ->join('partners_affiliates', 'partners_affiliates.id', '=', 'partners_affiliates_id')
                ->join('affiliates', 'partners_affiliates.affiliate_id', '=', 'affiliates.id')
                ->groupBy('partners_affiliates.id', 'affiliates.id', 'affiliates.domain_name', DB::raw('YEAR(click_ts), WEEK(click_ts)'))
                ->orderBy(DB::raw('YEAR(click_ts), WEEK(click_ts)'), 'asc')
                ->get();


            foreach ($results as $result) {
                $fromDate = Carbon::parse($result->from_date)->format('d M Y');
                $toDate = Carbon::parse($result->to_date)->format('d M Y');


                if (!isset($chartData[$result->domain_name])) {
                    $chartData[$result->domain_name] = [];
                }


                $chartData[$result->domain_name][] = [
                    'total_clicks' => $result->total_clicks,
                    'click_date' => 'Week ' . $result->week . ', ' . $result->year . ' (' . $fromDate . ' to ' . $toDate . ')',
                ];
            }
        } else {
            $results = $query->select(
                DB::raw('YEAR(click_ts) as year, MONTH(click_ts) as month, COUNT(*) as total_clicks'),
                'partners_affiliates.id as partner_affiliate_id',
                'affiliates.id as affiliate_id',
                'affiliates.domain_name'
            )
                ->join('partners_affiliates', 'partners_affiliates.id', '=', 'partners_affiliates_id')
                ->join('affiliates', 'partners_affiliates.affiliate_id', '=', 'affiliates.id')
                ->groupBy('partners_affiliates.id', 'affiliates.id', 'affiliates.domain_name', DB::raw('YEAR(click_ts), MONTH(click_ts)'))
                ->orderBy(DB::raw('YEAR(click_ts), MONTH(click_ts)'), 'asc')
                ->get();


            foreach ($results as $result) {
                $formattedDate = Carbon::createFromDate($result->year, $result->month)->format('M Y');


                if (!isset($chartData[$result->domain_name])) {
                    $chartData[$result->domain_name] = [];
                }


                $chartData[$result->domain_name][] = [
                    'total_clicks' => $result->total_clicks,
                    'click_date' => $formattedDate,
                ];
            }
        }

        $affiliate_domains = array_keys($chartData);

        $topN = $request->input('topN', 10);
        $topZipCodes = $this->getTopZipCodes($partner->id, $filter, $topN);

        $filterText = $this->getFilterText($filter);

        if ($request->has('download')) {
            $pdf = PDF::loadView('partner.clicks-report-pdf', compact('partner', 'dateFrom', 'dateTo', 'chartData', 'topZipCodes', 'dateFrom', 'dateTo', 'filterText', 'topN'));

            return $pdf->download('clicks_report.pdf');
        }
        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $company_info = ProviderData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $partner_plans = $partner->selected_plans ?? null;
        $selected_partner_plans = json_decode($partner_plans);
        $selected_plans = SelectedPlan::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        if ($selected_plans) {
            $selected_plan_id = $selected_plans->plan_id;
            $plans = Plans::where('price', '!=', '0')
                ->whereIn('plans.plan_id', $selected_partner_plans)
                ->where('plan_id', '!=', $selected_plan_id)->get();
        } else {
            $selected_plan_id = null;
            $plans = Plans::where('price', '!=', '0')->get();
        }
        $selected_plan = Plans::where('plan_id', $selected_plan_id)->first();
        $paymentmethod = PaymentMethod::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $startingDate = Carbon::parse($dateFrom)->format('d/m/Y');
        $endDate = Carbon::parse($dateTo)->format('d/m/Y');
        return view('admin/view/clicks-data', compact('partner', 'total_clicks', 'chartData', 'selected_plan', 'affiliate_domains', 'dateFrom', 'dateTo', 'paymentmethod', 'plans', 'topZipCodes', 'filterText', 'topN', 'filter', 'dataSplit', 'affiliates', 'availability_data', 'company_info', 'startingDate', 'endDate'));
    }


    public function viewPartnerClicksData(Request $request)
    {
        $id = Route::getCurrentRoute()->parameter('id');

        $partner = Partner::findOrFail($id);

        $zohoCustId = $partner->zoho_cust_id;


        $partnerAffiliateIds = PartnersAffiliates::where('partner_id', $partner->id)->pluck('id');

        $filter = $request->input('filter', 'mtd');

        $dataSplit = $request->input('data_split', 'daily');

        $now = Carbon::now();
        if ($filter === 'custom') {
            if ($request->has('date_from')) {
                $dateFrom = Carbon::parse($request->get('date_from'));
            }
            if ($request->has('date_to')) {
                $dateTo = Carbon::parse($request->get('date_to'));
            }
        } else {

            [$dateFrom, $dateTo] = $this->getDateRange($filter, $request, $now);
        }

        $daysInRange = $dateTo->diffInDays($dateFrom) + 1;

        $query = DB::table('clicks as c')
            ->whereIn('c.partners_affiliates_id', $partnerAffiliateIds)
            ->whereBetween('c.click_ts', [$dateFrom, $dateTo]);

        $chartData = $this->getChartData($query, $dataSplit);

        $subscription = Subscriptions::where('zoho_cust_id', $zohoCustId)->first();

        $totalCost = 0;

        $invoicePace = 0;

        $clicksPace = 0;



        if ($subscription) {

            $planId = $subscription->plan_id;

            $plan = Plans::where('plan_id', $planId)->first();

            if ($plan) {

                if ($plan->is_cpc) {

                    $now = Carbon::now();

                    $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');

                    $todayDate = $now->format('Y-m-d');

                    $todayDayNumber = $now->format('j');

                    $totalDaysInMonth = $now->daysInMonth;

                    $totalClicksTillDate = DB::table('clicks as c')
                        ->whereIn('c.partners_affiliates_id', $partnerAffiliateIds)
                        ->whereBetween('c.click_ts', [$startOfMonth, $todayDate])
                        ->count();

                    $perDayClicks = $totalClicksTillDate / $todayDayNumber;

                    $totalCost = $totalClicksTillDate * ($plan->price);

                    $invoicePace = $perDayClicks * $totalDaysInMonth * ($plan->price);

                    $clicksPace = $perDayClicks * $totalDaysInMonth;
                } else {

                    $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');

                    $todayDate = $now->format('Y-m-d');

                    $todayDayNumber = $now->format('j');

                    $totalDaysInMonth = $now->daysInMonth;

                    $totalClicksTillDate = DB::table('clicks as c')
                        ->whereIn('c.partners_affiliates_id', $partnerAffiliateIds)
                        ->whereBetween('c.click_ts', [$startOfMonth, $todayDate])
                        ->count();

                    $perDayClicks = $totalClicksTillDate / $todayDayNumber;

                    $totalCost = $totalClicksTillDate * ($plan->price);

                    $invoicePace = $perDayClicks * $totalDaysInMonth * ($plan->price);

                    $clicksPace = $perDayClicks * $totalDaysInMonth;
                }
            }
        }

        $metrics = DB::table('clicks as c')
            ->leftJoin('clicks_conversions as cc', 'cc.click_id', '=', 'c.id')
            ->leftJoin('partners_affiliates as pa', 'pa.id', '=', 'c.partners_affiliates_id')
            ->leftJoin('partners as p', 'p.id', '=', 'pa.partner_id')
            ->whereIn('c.partners_affiliates_id', $partnerAffiliateIds)
            ->whereBetween('c.click_ts', [$dateFrom, $dateTo])
            ->select(
                DB::raw('COUNT(c.id) as total_clicks'),
                DB::raw('COUNT(cc.id) as total_conversions'),
                DB::raw('(COUNT(cc.id) / COUNT(c.id)) * 100 as conversion_rate')
            )
            ->groupBy('p.id')
            ->first();

        // Ensure metrics exists
        if ($metrics) {
            $metrics->total_cost = $totalCost;
            $metrics->invoice_pace = $invoicePace;
            $metrics->clicks_pace = $clicksPace;
        }

        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $company_info = ProviderData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $partner_plans = $partner->selected_plans ?? null;
        $selected_partner_plans = json_decode($partner_plans);
        $selected_plans = SelectedPlan::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        if ($selected_plans) {
            $selected_plan_id = $selected_plans->plan_id;
            $plans = Plans::where('price', '!=', '0')
                ->whereIn('plans.plan_id', $selected_partner_plans)
                ->where('plan_id', '!=', $selected_plan_id)->get();
        } else {
            $selected_plan_id = null;
            $plans = Plans::where('price', '!=', '0')->get();
        }
        $selected_plan = Plans::where('plan_id', $selected_plan_id)->first();

        $paymentmethod = PaymentMethod::where('zoho_cust_id', $partner->zoho_cust_id)->first();

        $budget_cap = BudgetCapSettings::where('partner_id', $partner->id)->first();



        return view('admin.view.clicks-data', compact(
            'partner',
            'metrics',
            'chartData',
            'dateFrom',
            'dateTo',
            'daysInRange',
            'totalCost',
            'company_info',
            'availability_data',
            'partner_plans',
            'selected_plan',
            'plans',
            'paymentmethod',
            'budget_cap'
        ));
    }





    private function getDateRange($filter, $request, $now)
    {
        switch ($filter) {
            case 'mtd':
                $dateFrom = $now->copy()->startOfMonth();
                $dateTo = $now->copy()->subDay();
                break;
            case 'this_month':
                $dateFrom = $now->copy()->startOfMonth();
                $dateTo = $now->copy();
                break;
            case 'last_12_months':
                $dateFrom = $now->copy()->subMonths(12);
                $dateTo = $now->copy();
                break;
            case 'last_6_months':
                $dateFrom = $now->copy()->subMonths(6);
                $dateTo = $now->copy();
                break;
            case 'last_3_months':
                $dateFrom = $now->copy()->subMonths(3);
                $dateTo = $now->copy();
                break;
            case 'last_1_month':
                $dateFrom = $now->copy()->subMonth();
                $dateTo = $now->copy();
                break;
            case 'last_7_days':
                $dateFrom = $now->copy()->subDays(7);
                $dateTo = $now->copy();
                break;
            case '-':
                $dateFrom = Carbon::parse($request->get('date_from'));
                $dateTo = Carbon::parse($request->get('date_to'));
                break;
            default:
                $dateFrom = $now->copy()->subMonths(12);
                $dateTo = $now->copy();
                break;
        }

        return [$dateFrom, $dateTo];
    }

    private function getChartData($query, $dataSplit)
    {
        $chartData = [];

        if ($dataSplit === 'daily') {

            $results = $query->select(
                DB::raw('DATE(c.click_ts) as date'),
                DB::raw('COUNT(c.id) as total_clicks'),
                'a.domain_name as domain'
            )
                ->leftJoin('partners_affiliates as pa', 'pa.id', '=', 'c.partners_affiliates_id')
                ->leftJoin('affiliates as a', 'a.id', '=', 'pa.affiliate_id')
                ->groupBy(DB::raw('DATE(c.click_ts)'), 'a.domain_name')
                ->orderBy(DB::raw('DATE(c.click_ts)'), 'asc')
                ->get();

            $domainClicks = [];

            foreach ($results as $result) {

                $formattedDate = Carbon::parse($result->date)->format('d M Y');

                if (!isset($domainClicks[$formattedDate])) {

                    $domainClicks[$formattedDate] = [];
                }
                $domainClicks[$formattedDate][$result->domain] = $result->total_clicks;
            }

            foreach ($domainClicks as $date => $domains) {
                $chartData[] = [
                    'date' => $date,
                    'total_clicks' => array_sum($domains),
                    'domain_clicks' => $domains,
                ];
            }
        } elseif ($dataSplit === 'weekly') {

            // Adjust the query to group by week (year and week number)
            $results = $query->select(
                DB::raw('YEAR(c.click_ts) as year'), // Extract the year
                DB::raw('WEEK(c.click_ts) as week'), // Extract the week number
                DB::raw('COUNT(c.id) as total_clicks'),
                DB::raw('MIN(click_ts) as from_date'),
                DB::raw(' MAX(click_ts) as to_date'), // Count clicks
                'a.domain_name as domain' // Get domain name
            )
                ->leftJoin('partners_affiliates as pa', 'pa.id', '=', 'c.partners_affiliates_id')
                ->leftJoin('affiliates as a', 'a.id', '=', 'pa.affiliate_id')
                ->groupBy(DB::raw('YEAR(c.click_ts)'), DB::raw('WEEK(c.click_ts)'), 'a.domain_name') // Group by year and week
                ->orderBy(DB::raw('YEAR(c.click_ts)'), 'asc') // Order by year and week
                ->orderBy(DB::raw('WEEK(c.click_ts)'), 'asc') // Ensure proper weekly ordering
                ->get();

            $domainClicks = [];

            foreach ($results as $result) {
                $fromDate = Carbon::parse($result->from_date)->format('d M Y');
                $toDate = Carbon::parse($result->to_date)->format('d M Y');

                // Create a readable format for week by combining year and week number
                $formattedWeek = $fromDate . ' to ' . $toDate;

                if (!isset($domainClicks[$formattedWeek])) {
                    $domainClicks[$formattedWeek] = [];
                }
                $domainClicks[$formattedWeek][$result->domain] = $result->total_clicks;
            }

            foreach ($domainClicks as $week => $domains) {
                $chartData[] = [
                    'date' => $week, // Use the formatted week
                    'total_clicks' => array_sum($domains), // Sum up clicks for all domains
                    'domain_clicks' => $domains, // List clicks per domain
                ];
            }
        } else {

            // Adjust the query to group by month and year, and format the date as "YYYY-MM"
            $results = $query->select(
                DB::raw('DATE_FORMAT(c.click_ts, "%Y-%m") as month_year'), // Format as "YYYY-MM" (e.g., "2024-01")
                DB::raw('COUNT(c.id) as total_clicks'), // Count clicks
                'a.domain_name as domain' // Get domain name
            )
                ->leftJoin('partners_affiliates as pa', 'pa.id', '=', 'c.partners_affiliates_id')
                ->leftJoin('affiliates as a', 'a.id', '=', 'pa.affiliate_id')
                ->groupBy(DB::raw('DATE_FORMAT(c.click_ts, "%Y-%m")'), 'a.domain_name') // Group by formatted "YYYY-MM"
                ->orderBy(DB::raw('DATE_FORMAT(c.click_ts, "%Y-%m")'), 'asc') // Order by year and month
                ->get();

            $domainClicks = [];

            foreach ($results as $result) {

                // The formatted month-year string will be like "2024-01"
                $formattedMonth = \Carbon\Carbon::parse($result->month_year)->format('F Y'); // Convert to "January 2024"

                if (!isset($domainClicks[$formattedMonth])) {
                    $domainClicks[$formattedMonth] = [];
                }
                $domainClicks[$formattedMonth][$result->domain] = $result->total_clicks;
            }

            foreach ($domainClicks as $month => $domains) {
                $chartData[] = [
                    'date' => $month, // Display "January 2024" or similar
                    'total_clicks' => array_sum($domains), // Sum up clicks for all domains in that month
                    'domain_clicks' => $domains, // List clicks per domain
                ];
            }
        }


        return $chartData;
    }




    public function viewPartnerClicksDataOld(Request $request)
    {


        $id = Route::getCurrentRoute()->id;
        $partner = Partner::where('id', $id)->first();
        $affiliate_id = PartnersAffiliates::where('partner_id', $partner->id)->pluck('affiliate_id');
        $affiliates = Affiliates::whereIn('id', $affiliate_id)->get();
        $values = [];

        foreach ($affiliates as $affiliate) {
            array_push($values, $affiliate->isp_affiliate_id . "(" . $affiliate->domain_name . ")");
        }

        $partnerId = $partner->zoho_cust_id;

        $affiliateIdsArray = [''];

        $affiliate_ids = $request->input('affiliate_ids', []);


        if ($affiliate_ids) {
            $affiliate_array_value = $affiliate_ids[0];
            $affiliate_value = explode(',', $affiliate_array_value);
            $values = array_unique($affiliate_value);
        }

        if ($affiliate_ids === []) {
            $partneraffiliateIds = PartnersAffiliates::where('partner_id', $partner->id)->pluck('id');
        } else if ($affiliate_ids[0] === null) {
            $partneraffiliateIds = [];
        }



        if ($affiliate_ids) {
            if ($affiliate_ids[0] !== null) {
                $affiliateIds = $request->affiliate_ids[0];

                $pattern = '/(\d+)\(/';;

                preg_match_all($pattern, $affiliateIds, $matches);

                $affiliateIdsArray = $matches[1];

                $affiliate = Affiliates::whereIn('isp_affiliate_id', $affiliateIdsArray)->pluck('id');

                $partneraffiliateIds = PartnersAffiliates::whereIn('affiliate_id', $affiliate)
                    ->where('partner_id', $partner->id)->pluck('id');
            }
        }


        $query = Clicks::whereIn('partners_affiliates_id', $partneraffiliateIds);

        $now = Carbon::now();
        $filter = $request->input('filter', 'mtd');
        $dataSplit = $request->input('data_split', 'daily');
        $dateFrom = null;
        $dateTo = null;

        if ($request->has('date_from')) {
            $dateFrom = Carbon::parse($request->get('date_from'));
        }
        if ($request->has('date_to')) {
            $dateTo = Carbon::parse($request->get('date_to'));
        }

        switch ($filter) {
            case 'mtd':
                $dateFrom =  $now->copy()->startOfMonth();
                $dateTo =  $now->copy()->subDay();
                $dataSplit = 'daily';
                break;
            case 'this_month':
                $dateFrom = $now->copy()->startOfMonth();
                $dateTo = $now->copy();
                $dataSplit = 'daily';
                break;
            case 'last_12_months':
                $dateFrom = $now->copy()->subMonths(12);
                $dateTo = $now->copy();
                break;
            case 'last_6_months':
                $dateFrom = $now->copy()->subMonths(6);
                $dateTo = $now->copy();
                break;
            case 'last_3_months':
                $dateFrom = $now->copy()->subMonths(3);
                $dateTo = $now->copy();
                break;
            case 'last_1_month':
                $dateFrom =  $now->copy()->subMonth();
                $dateTo = $now->copy();
                break;
            case 'last_7_days':
                $dateFrom = $now->copy()->subDays(7);
                $dateTo = $now->copy();
                break;
            case '-':
                $dateFrom = Carbon::parse($request->get('date_from'));
                $dateTo = Carbon::parse($request->get('date_to'));
                break;
            default:
                $dateFrom = $now->copy()->subMonths(12);
                $dateTo = $now->copy();
                break;
        }


        $query->where('click_ts', '>=', $dateFrom)
            ->where('click_ts', '<=', $dateTo);

        $chartData = [];

        if ($dataSplit == 'daily') {
            $results = $query->select(
                DB::raw('DATE(click_ts) as date,COUNT(*) as total_clicks'),
                'partners_affiliates.id as partner_affiliate_id',
                'affiliates.id as affiliate_id',
                'affiliates.domain_name'
            )
                ->join('partners_affiliates', 'partners_affiliates.id', '=', 'partners_affiliates_id')
                ->join('affiliates', 'partners_affiliates.affiliate_id', '=', 'affiliates.id')
                ->groupBy('partners_affiliates.id', 'affiliates.id', 'affiliates.domain_name', DB::raw('DATE(click_ts)'))
                ->orderBy(DB::raw('DATE(click_ts)'), 'asc')
                ->get();


            foreach ($results as $result) {
                $formattedDate = Carbon::parse($result->date)->format('d M Y');
                $chartData[] = [
                    'partner_affiliate_id' => $result->partner_affiliate_id,
                    'affiliate_id' => $result->affiliate_id,
                    'domain_name' => $result->domain_name,
                    'total_clicks' => $result->total_clicks,
                    'click_date' => $formattedDate,
                ];
            }
            dd($chartData);
        } elseif ($dataSplit == 'weekly') {
            $results = $query->select(DB::raw('YEAR(click_ts) as year, WEEK(click_ts) as week, MIN(click_ts) as from_date, MAX(click_ts) as to_date, COUNT(*) as total_clicks'))
                ->groupBy(DB::raw('YEAR(click_ts), WEEK(click_ts)'))
                ->orderBy(DB::raw('YEAR(click_ts), WEEK(click_ts)'), 'asc')
                ->get();

            foreach ($results as $result) {
                $fromDate = Carbon::parse($result->from_date)->format('d M Y');
                $toDate = Carbon::parse($result->to_date)->format('d M Y');
                $chartData[] = [
                    'click_date' => 'Week ' . $result->week . ', ' . $result->year . ' (' . $fromDate . ' to ' . $toDate . ')',
                    'click_count' => $result->total_clicks,
                ];
            }
        } else {
            $results = $query->select(DB::raw('YEAR(click_ts) as year, MONTH(click_ts) as month, COUNT(*) as total_clicks'))
                ->groupBy(DB::raw('YEAR(click_ts), MONTH(click_ts)'))
                ->orderBy(DB::raw('YEAR(click_ts), MONTH(click_ts)'), 'asc')
                ->get();
            foreach ($results as $result) {
                $formattedDate = Carbon::createFromDate($result->year, $result->month)->format('M Y');
                $chartData[] = [
                    'click_date' => $formattedDate,
                    'click_count' => $result->total_clicks,
                ];
            }
        }


        $topN = $request->input('topN', 10);
        $topZipCodes = $this->getTopZipCodes($partner->id, $filter, $topN);

        $filterText = $this->getFilterText($filter);

        if ($request->has('download')) {
            $pdf = PDF::loadView('partner.clicks-report-pdf', compact('partner', 'chartData', 'topZipCodes', 'filterText', 'topN'));

            return $pdf->download('clicks_report.pdf');
        }
        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $company_info = ProviderData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $partner_plans = $partner->selected_plans ?? null;
        $selected_partner_plans = json_decode($partner_plans);
        $selected_plans = SelectedPlan::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        if ($selected_plans) {
            $selected_plan_id = $selected_plans->plan_id;
            $plans = Plans::where('price', '!=', '0')
                ->whereIn('plans.plan_id', $selected_partner_plans)
                ->where('plan_id', '!=', $selected_plan_id)->get();
        } else {
            $selected_plan_id = null;
            $plans = Plans::where('price', '!=', '0')->get();
        }
        $selected_plan = Plans::where('plan_id', $selected_plan_id)->first();
        $paymentmethod = PaymentMethod::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        return view('admin/view/clicks-data', compact('partner', 'chartData', 'selected_plan', 'paymentmethod', 'plans', 'topZipCodes', 'filterText', 'topN', 'filter', 'dataSplit', 'affiliates', 'affiliateIdsArray', 'values', 'availability_data', 'company_info', 'dateFrom', 'dateTo'));
    }

    private function getTopZipCodes($partnerId, $filter, $topN)
    {
        $now = Carbon::now();
        $affiliateIds = PartnersAffiliates::where('partner_id', $partnerId)->pluck('id');

        $query = Clicks::select('intended_zip', DB::raw('COUNT(*) as total_clicks'))
            ->whereIn('partners_affiliates_id', $affiliateIds);

        switch ($filter) {
            case 'last_12_months':
                $query->where('click_ts', '>=', $now->copy()->subMonths(12));
                break;
            case 'last_6_months':
                $query->where('click_ts', '>=', $now->copy()->subMonths(6));
                break;
            case 'last_3_months':
                $query->where('click_ts', '>=', $now->copy()->subMonths(3));
                break;
            case 'last_1_month':
                $query->where('click_ts', '>=', $now->copy()->subMonth());
                break;
            case 'last_7_days':
                $query->where('click_ts', '>=', $now->copy()->subDays(7));
                break;
            default:
                $query->where('click_ts', '>=', $now->copy()->subMonths(12));
                break;
        }

        return $query->groupBy('intended_zip')
            ->orderByDesc('total_clicks')
            ->take($topN)
            ->get();
    }

    private function getFilterText($filter)
    {
        switch ($filter) {
            case 'last_12_months':
                return 'Last 12 Months';
            case 'last_6_months':
                return 'Last 6 Months';
            case 'last_3_months':
                return 'Last 3 Months';
            case 'last_1_month':
                return 'Last Month';
            case 'last_7_days':
                return 'Last 7 Days';
            default:
                return 'Last 12 Months';
        }
    }

    public function exportClicksReport($id, Request $request)
    {
        $partner = Partner::findOrFail($id);
        $zohoCustId = $partner->zoho_cust_id;
        $partnerAffiliateIds = PartnersAffiliates::where('partner_id', $partner->id)->pluck('id');

        // Handle filter and date range
        $filter = $request->input('filter', 'mtd');
        $dataSplit = $request->input('data_split', 'daily');
        $now = Carbon::now();
        [$dateFrom, $dateTo] = $this->getDateRange($filter, $request, $now);

        // Fetch the click data
        $query = DB::table('clicks as c')
            ->whereIn('c.partners_affiliates_id', $partnerAffiliateIds)
            ->whereBetween('c.click_ts', [$dateFrom, $dateTo]);

        // Get chart data
        $chartData = $this->getChartData($query, $dataSplit);

        // Prepare the CSV output
        $csv = Writer::createFromFileObject(new SplTempFileObject(), 'w+');
        $csv->insertOne(['Date', 'Total Clicks', 'Domain Clicks']); // Column headers

        // Add data rows to the CSV
        foreach ($chartData as $data) {
            $row = [$data['date'], $data['total_clicks']];
            foreach ($data['domain_clicks'] as $domain => $clicks) {
                $row[] = "$domain: $clicks"; // Format domain clicks
            }
            $csv->insertOne($row);
        }

        // Set CSV headers for download
        $filename = $partner->company_name . '_clicks_report_' . $partner->id . '_' . $dateFrom->format('Y_m_d') . '_to_' . $dateTo->format('Y_m_d') . '.csv';
        $response = new Response($csv->getContent());
        $response->header('Content-Type', 'text/csv');
        $response->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }


    public function updateBillingAddress(Request $request, AccessToken $token)
    {

        $app_url = env('APP_URL');
        $token1 = AccessToken::latest('created_at')->first();
        if ($token1 !== null) {
            $access_token = $token1->access_token;
        } else {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
            $access_token = $token1->access_token;
            return back()->with('fail', 'Kindly Try Again');
        }
        $partner_url = env('PARTNER_URL');

        $partner = Partner::where('zoho_cust_id', $request->zoho_cust_id)->first();

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
                        "street" => $request->address,
                        "city" => $request->city,
                        "state" => $request->state,
                        "zip" => $request->zip_code,
                        "country" => $request->country,
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

            $partner_address = PartnerAddress::where('zoho_cust_id', $request->zoho_cust_id)->first();

            $partner_address->street = $address->street;
            $partner_address->city = $address->city;
            $partner_address->state = $address->state;
            $partner_address->country = $address->country;
            $partner_address->zip_code = $address->zip;
            $partner_address->save();


            return redirect('/admin/view-partner/' . $partner->id)->with('success', 'Billing Address updated successfully');
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);
            $response = json_decode($response);

            if ($response->message === "You are not authorized to perform this operation") {

                $token->getToken();

                return redirect('/')->with('fail', 'Kindly Subscribe Again!');
            }
        }
    }

    public function uploadProviderData(Request $request)
    {

        $partner = Partner::where('id', $request->partner_id)->first();
        $admin = Admin::where('id', Session::get('loginId'))->first();

        if ($request->has('logo')) {
            $file = $request->file('logo');
            $partner_company_name = $partner->company_name;
            $formatted_company_name = str_replace(' ', '_', $partner_company_name);
            $formatted_company_name = strtolower($formatted_company_name);
            $filename = $formatted_company_name;
            $extension = $file->getClientOriginalExtension();
            $filename .= '.' . $extension;
            $timestamp = now()->format('YmdHis');
            $path =  $partner->zoho_cust_id . '/partner-logo/' . $timestamp . '/';
            $logo_object_path = $path . $filename;
            Storage::disk('s3')->put($logo_object_path, file_get_contents($file));

            $data = new ProviderData();
            $data->logo_image = $path . $filename;
            $data->landing_page_url = $request->landing_page_url;
            $data->company_name = $request->company_name;
            $data->tune_link = $request->tune_link;
            $data->zoho_cust_id = $partner->zoho_cust_id;
            $data->uploaded_by = $admin->admin_name . '(admin)';
            $data->save();

            return back()->with('success', 'Provider Data Uploaded Successfully');
        }
    }


    public function uploadProviderAvailabilityData(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $partner = Partner::where('id', $request->partner_id)->first();
        $admin = Admin::where('id', Session::get('loginId'))->first();

        if (!$partner) {
            return response()->json(['success' => false, 'message' => 'Partner record not found'], 404);
        }

        if ($request->hasFile('csv_file')) {

            $cleaned_data = [];

            $unique_rows = [];

            $csv_file = $request->file('csv_file');
            $timestamp = now()->format('YmdHis');

            try {
                $csv_content = file_get_contents($csv_file->path());
                $rows = explode("\n", $csv_content);

                if (count($rows) > 0) {
                    $header = str_getcsv($rows[0], ',');
                    $header = str_getcsv($rows[0], ',');

                    $header = array_map(function ($col) {
                        return trim(str_replace("\xEF\xBB\xBF", '', $col));
                    }, $header);

                    if ($header[0] === 'ZIP' && $header[1] === 'Speed' && $header[2] === 'Type' && $header[3] === 'Coverage' && $header[4] === 'CustomerType') {

                        // Add header to cleaned data if it's valid
                        $cleaned_data[] = $header;
                    } else {


                        throw new \Exception('Invalid CSV header. Expected columns are ZIP, Speed, Type, Coverage, CustomerType.');
                    }
                }

                foreach ($rows as $index => $row) {

                    $data = str_getcsv($row, ',');
                    if ($index === 0) {

                        continue;
                    }

                    $data = str_getcsv($row, ',');

                    if (count($data) === 5) {
                        // Format ZIP code to 5 digits
                        $data[0] = str_pad($data[0], 5, '0', STR_PAD_LEFT);

                        $unique_key = $data[0] . '-' . $data[2] . '-' . $data[4];

                        if (!isset($unique_rows[$unique_key])) {
                            $unique_rows[$unique_key] = true;
                            $cleaned_data[] = $data;
                        }
                    }
                }

                $cleaned_csv_content = implode("\n", array_map(function ($row) {
                    return implode(',', $row);
                }, $cleaned_data));


                $cleaned_csv_filename = 'zip_list_template.csv';

                $cleaned_csv_path = $partner->zoho_cust_id . '/aoafile/' . $timestamp . '/';
                $csv_object_path = $cleaned_csv_path . $cleaned_csv_filename;

                Storage::disk('s3')->put($csv_object_path, $cleaned_csv_content);

                $client = new S3Client([
                    'version' => 'latest',
                    'region'  => env('AWS_DEFAULT_REGION'),
                ]);

                $bucket = env('AWS_BUCKET');

                $result = $client->headObject([
                    'Bucket' => $bucket,
                    'Key'    => $csv_object_path,
                ]);

                $fileSize = $result['ContentLength'];

                $providerAvailabilityData = new ProviderAvailabilityData();
                $providerAvailabilityData->file_size = $fileSize;
                $providerAvailabilityData->file_name = $cleaned_csv_filename;
                $providerAvailabilityData->zip_count = count($unique_rows);
                $providerAvailabilityData->url = $csv_object_path;
                $providerAvailabilityData->zoho_cust_id = $partner->zoho_cust_id;
                $providerAvailabilityData->uploaded_by = $admin->admin_name . '(admin)';
                $providerAvailabilityData->save();

                Storage::disk('local')->delete($csv_file->path());

                return redirect('/admin/view-partner/' . $partner->id . '/provider-data')->with('success', 'Provider Availability Data Uploaded Successfully');
            } catch (\Exception $e) {
                \Log::error('Error processing CSV: ' . $e->getMessage());
                return back()->with('fail', 'Error processing CSV: ' . $e->getMessage());
            }
        }
        return back()->with('fail', 'Please upload a valid CSV file');
    }


    public function updatePartner(AccessToken $token, Request $request)
    {
        $token1 = AccessToken::latest('created_at')->first();
        if ($token1 !== null) {
            $access_token = $token1->access_token;
        } else {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
            $access_token = $token1->access_token;
            return back()->with('fail', 'Kindly Try Again');
        }
        $partner_url = env('PARTNER_URL');

        $partner = Partner::where('zoho_cust_id', $request->zoho_cust_id)->first();

        $client = new \GuzzleHttp\Client();

        try {
            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                    'content-type: application/json'
                ],
                'json' => [
                    "display_name" => $request->company_name,
                    "company_name" => $request->company_name,
                    "custom_fields" => [
                        [
                            "label" => "isp_advertiser_id",
                            "value" => $request->advertiser_id,
                        ],
                        [
                            "label" => "isp_tax_number",
                            "value" => $request->tax_number
                        ]
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

            $zoho_partner = $response->customer;

            $partner->company_name = $zoho_partner->company_name;
            $partner->isp_advertiser_id = $request->advertiser_id;
            $partner->tax_number = $request->tax_number;
            $partner->id = $request->partner_id;
            $partner->payment_gateway = $request->payment_gateway;
            $partner->save();

            return redirect('/admin/view-partner/' . $partner->id)->with('success', 'Partner updated successfully');
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);
            $response = json_decode($response);

            if ($response->message === "You are not authorized to perform this operation") {

                $token->getToken();

                return redirect('/')->with('fail', 'Kindly Subscribe Again!');
            }
        }
    }

    public function updateSubscription(Request $request, AccessToken $token)
    {
        $partner = Partner::where('zoho_cust_id', $request->partner_id)->first();
        $partner_primary_user = PartnerUsers::where('zoho_cust_id', $partner->zoho_cust_id)->where('is_primary', true)->first();
        $subscription = Subscriptions::where('zoho_cust_id', $partner->zoho_cust_id)->where('status', 'live')->first();
        $plan_sub = Plans::where('plan_code', $request->plan_code)->first();
        $update_url = env('UPDATE_SUBSCRIPTION_URL');
        $app_url = env('APP_URL');
        $token1 = AccessToken::latest('created_at')->first();

        if ($token1 !== null) {

            $access_token = $token1->access_token;
        } else {

            $token->getToken();

            $token1 = AccessToken::latest('created_at')->first();
            $access_token = $token1->access_token;
        }

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
                    "plan_code" => $plan_sub->plan_code,
                    "price" => $plan_sub->price,
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

        $partner_email = $partner_primary_user->email;

        $partner_name = $partner_primary_user->first_name;

        $plan_name = $plan_sub->plan_name;

        $plan_price = $plan_sub->price;

        try {

            Mail::to($partner_email)->send(new CreateSubscription($partner_email, $partner_name, $plan_name, $plan_price, $pay_link));

            return back()->with('success', 'Subscription Mail sent successfully!');
        } catch (\Exception $e) {

            \Log::error("Error sending subscription email: " . $e->getMessage());

            return redirect('/admin/subscription')->with('fail', 'There was a problem sending the email. Please try again later.');
        }
    }

    public function disablePartner(Subscriptions $subscriptions, AccessToken $token)
    {

        $token1 = AccessToken::latest('created_at')->first();

        if ($token1 !== null) {

            $access_token = $token1->access_token;
        } else {

            $token->getToken();

            $token1 = AccessToken::latest('created_at')->first();
            $access_token = $token1->access_token;

            return back()->with('fail', 'Kindly Try Again');
        }
        $id = Route::getCurrentRoute()->id;
        $partner = Partner::where('id', $id)->first();

        $subscription = Subscriptions::where('zoho_cust_id', $partner->zoho_cust_id)->where('status', 'live')->first();
        $paymentMethod = PaymentMethod::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $free_plan = Plans::where('price', '0')->first();



        if ($subscription) {
            $subscriptions->dowgradeToFreePlan($access_token, $paymentMethod, $free_plan, $subscription);
            $subscriptions->cancelSubscription($access_token, $subscription);
            $this->disablePartner1($access_token, $partner);
            $partner->status = "inactive";
            $partner->save();
            return redirect('/admin/view-partner/' . $partner->id)->with('success', 'Partner marked as inactive successsfully');
        } else {
            $this->disablePartner1($access_token, $partner);
            $partner->status = "inactive";
            $partner->save();
            return redirect('/admin/view-partner/' . $partner->id)->with('success', 'Partner marked as inactive successsfully');
        }
    }

    public function reactivatePartner(AccessToken $token)
    {
        $token1 = AccessToken::latest('created_at')->first();

        if ($token1 !== null) {

            $access_token = $token1->access_token;
        } else {

            $token->getToken();

            $token1 = AccessToken::latest('created_at')->first();
            $access_token = $token1->access_token;

            return back()->with('fail', 'Kindly Try Again');
        }
        $id = Route::getCurrentRoute()->id;
        $partner = Partner::where('id', $id)->first();

        $partner_url = env('PARTNER_URL');
        $client = new \GuzzleHttp\Client();

        $options = [
            'headers' => [
                'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID')
            ]
        ];

        $reactive_url = $partner_url . '/' . $partner->zoho_cust_id . '/markasactive';

        $res = $client->request(
            'POST',
            $reactive_url,
            $options
        );

        $response = (string) $res->getBody();
        $response = json_decode($response);

        $partner->status = "active";
        $partner->save();
        return redirect('/admin/view-partner/' . $partner->id)->with('success', 'Partner marked as active successsfully');
    }


    public function disablePartner1($access_token, $partner)
    {
        $partner_url = env('PARTNER_URL');
        $client = new \GuzzleHttp\Client();

        $options = [
            'headers' => [
                'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID')
            ]
        ];


        $disable_url = $partner_url . '/' . $partner->zoho_cust_id . '/markasinactive';

        $res = $client->request(
            'POST',
            $disable_url,
            $options
        );

        $response = (string) $res->getBody();
        $response = json_decode($response);
    }

    public function addAffiliate(Request $request)
    {
        $affiliateIds = $request->affiliate_ids[0];

        $pattern = '/(\d+)\(/';;

        preg_match_all($pattern, $affiliateIds, $matches);

        $affiliateIdsArray = $matches[1];

        $affiliateIdsFromTable = Affiliates::whereIn('isp_affiliate_id', $affiliateIdsArray)
            ->pluck('id');


        foreach ($affiliateIdsFromTable as $affiliateId) {
            $partner_affiliate = new PartnersAffiliates();
            $partner_affiliate->affiliate_id = $affiliateId;
            $partner_affiliate->partner_id = $request->partner_id;
            $partner_affiliate->save();
        }
        return back()->with('success', 'Affiliate Added Successfully');
    }

    public function removeAffiliate(Request $request)
    {
        $affiliateIds = $request->affiliate_ids[0];

        $pattern = '/(\d+)\(/';;

        preg_match_all($pattern, $affiliateIds, $matches);

        $affiliateIdsArray = $matches[1];

        $affiliateIdsFromTable = Affiliates::whereIn('isp_affiliate_id', $affiliateIdsArray)
            ->pluck('id');

        foreach ($affiliateIdsFromTable as $affiliateId) {
            $partner_affiliate = PartnersAffiliates::where('affiliate_id', $affiliateId)->where('partner_id', $request->partner_id)->first();
            $partner_affiliate->delete();
        }
        return back()->with('success', 'Affiliate Removed Successfully');
    }

    public function approvePartner()
    {
        $id = Route::getCurrentRoute()->id;
        $partner = Partner::where('id', $id)->first();
        $partner->is_approved = true;
        $partner->save();
        return back()->with('success', 'Partner Approved Successfully');
    }

    public function addCustomInvoice(Request $request, AccessToken $token)
    {
        $subscription_url = env('SUBSCRIPTION_URL');
        try {

            $token1 = AccessToken::latest('created_at')->first();
            if ($token1 !== null) {
                $access_token = $token1->access_token;
            } else {
                $token->getToken();
                $token1 = AccessToken::latest('created_at')->first();
                $access_token = $token1->access_token;
                return back()->with('fail', 'Kindly Try Again');
            }

            $client = new \GuzzleHttp\Client();

            $options = [
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    "amount" => $request->amount,
                    "description" => $request->description,
                ]
            ];



            $add_charge_url = $subscription_url . $request->subscription_id . '/charge';


            $res = $client->request(
                'POST',
                $add_charge_url,
                $options
            );

            $data = json_decode($res->getBody()->getContents());

            if ($data->invoice->payments) {
                $payment_method = "card";
            } else {
                $payment_method = "bank_account";
            }

            $invoice = new Invoices();

            $invoice->invoice_id = $data->invoice->invoice_id;
            $invoice->invoice_number = $data->invoice->number;
            $invoice->invoice_date = $data->invoice->invoice_date;
            $invoice->credits_applied = $data->invoice->credits_applied;
            $invoice->discount = $data->invoice->invoice_items[0]->discount_amount;
            $invoice->payment_method = $payment_method;
            $invoice->payment_made = $data->invoice->payment_made;
            $invoice->invoice_link = $data->invoice->invoice_url;
            $invoice->zoho_cust_id = $data->invoice->customer_id;
            $invoice->balance = $data->invoice->balance;
            $invoice->status = $data->invoice->status;
            $invoice->subscription_id = [
                "subscription_id" => $data->invoice->subscriptions[0]->subscription_id,
            ];
            $invoice->invoice_items = [
                "code" =>  $data->invoice->invoice_items[0]->code,
                "quantity" => $data->invoice->invoice_items[0]->quantity,
                "item_id" => $data->invoice->invoice_items[0]->item_id,
                "discount_amount" => $data->invoice->invoice_items[0]->discount_amount,
                "tax_name" => $data->invoice->invoice_items[0]->tax_name,
                "description" => $data->invoice->invoice_items[0]->description,
                "item_total" => $data->invoice->invoice_items[0]->item_total,
                "item_custom_fields" => $data->invoice->invoice_items[0]->item_custom_fields,
                "tax_id" => $data->invoice->invoice_items[0]->tax_id,
                "tags" => $data->invoice->invoice_items[0]->tags,
                "unit" => $data->invoice->invoice_items[0]->unit,
                "account_id" => $data->invoice->invoice_items[0]->account_id,
                "tax_type" => $data->invoice->invoice_items[0]->tax_type,
                "price" => $data->invoice->invoice_items[0]->price,
                "product_id" => $data->invoice->invoice_items[0]->product_id,
                "account_name" => $data->invoice->invoice_items[0]->account_name,
                "name" => $data->invoice->invoice_items[0]->name,
                "tax_percentage" => $data->invoice->invoice_items[0]->tax_percentage,
            ];


            if ($data->invoice->payments) {

                $invoice->payment_details =  [
                    'payment_id' => $data->invoice->payments[0]->payment_id,
                    'payment_mode' => $data->invoice->payments[0]->payment_mode,
                    'reference_number' => $data->invoice->payments[0]->reference_number,
                    'payment_date' =>  $data->invoice->payments[0]->date,
                    'payment_amount' => $data->invoice->payments[0]->amount,

                ];
            }

            $invoice->save();

            return back()->with('success', 'Custom Invoice added successfully');
        } catch (\Exception $e) {

            return back()->with('fail', 'Failed to add custom invoice: ' . $e->getMessage());
        }
    }

    public function associatePaymentMethod(AccessToken $token)
    {

        $id = Route::getCurrentRoute()->id;
        $partner = Partner::where('id', $id)->first();
        $app_url = env('APP_URL');
        $token1 = AccessToken::latest('created_at')->first();
        if ($token1 !== null) {
            $access_token = $token1->access_token;
        } else {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
            $access_token = $token1->access_token;
            return back()->with('fail', 'Kindly Try Again');
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
                    "customer_id" => $partner->zoho_cust_id,

                    "redirect_url" => "$app_url/add-payment-method",
                ]
            ];
            $add_payment_url = env('HOSTEDPAGE_URL') . "addpaymentmethod";
            $res = $client->request(
                'POST',
                $add_payment_url,
                $options
            );

            $response = (string) $res->getBody();
            $response = json_decode($response);

            $hostedpage = $response->hostedpage;

            $pay_link = "https://billing.zoho.com/hostedpage/" . $hostedpage->hostedpage_id . "/checkout";

            $primary_user = PartnerUsers::where('zoho_cust_id', $partner->zoho_cust_id)->where('is_primary', true)->first();

            $partner_email = $primary_user->email;

            $partner_name = $primary_user->first_name;
            try {

                Mail::to($partner_email)->send(new AssociatePaymentMethod($partner_email, $partner_name, $pay_link));

                return back()->with('success', 'Associate Payment Method Mail sent successfully!');
            } catch (\Exception $e) {

                \Log::error("Error sending subscription email: " . $e->getMessage());

                return back()->with('fail', $e->getMessage());
            }
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);

            $response = json_decode($response);

            if ($response) {

                return redirect('/logout')->with('fail', 'You have to login first');
            }
        }
    }

    public function addCreditNote(Request $request, AccessToken $token)
    {
        $plan_code = $request->plan_code;

        $description = $request->description;

        $amount =  $request->amount;

        $partner = Partner::where('id', $request->partner_id)->first();

        $token1 = AccessToken::latest('created_at')->first();

        if ($token1 !== null) {
            $access_token = $token1->access_token;
        } else {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
            $access_token = $token1->access_token;
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
                    "customer_id" => $partner->zoho_cust_id,
                    "creditnote_items" => [
                        [
                            "description" => $description,
                            "code" => $plan_code,
                            "price" => $amount,
                            "quantity" => 1
                        ]
                    ]

                ]
            ];

            $create_creditnote_url = env('CREDITNOTES_URL');

            $res = $client->request(
                'POST',
                $create_creditnote_url,
                $options
            );

            $response = (string) $res->getBody();

            $response = json_decode($response);

            $data = $response->creditnote;

            $creditNote = new CreditNotes();

            $creditNote->creditnote_id = $data->creditnote_id;
            $creditNote->creditnote_number = $data->creditnote_number;
            $creditNote->credited_date = $data->date;
            if ($data->invoices) {
                $creditNote->invoice_number = $data->invoices[0]->invoice_number;
            } else {
                $creditNote->invoice_number = "-";
            }
            $creditNote->credited_amount = $data->total;
            $creditNote->balance = $data->balance;
            $creditNote->status = $data->status;
            $creditNote->zoho_cust_id = $data->customer_id;
            $creditNote->save();

            return back()->with('success', 'Credit Note Added Successfully');
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);

            $response = json_decode($response);

            if ($response) {

                return redirect('/logout')->with('fail', 'You have to login first');
            }
        }
    }

    public function refundPayment(Request $request, AccessToken $token, CreditNotes $creditnote)
    {
        $payment_id = $request->payment_id;
        $description = $request->description;
        $amount =  $request->amount;
        $invoice = Invoices::where('payment_details->payment_id', $payment_id)->first();
        $token1 = AccessToken::latest('created_at')->first();

        if ($token1 !== null) {
            $access_token = $token1->access_token;
        } else {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
            $access_token = $token1->access_token;
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
                    "amount" => $amount,
                    "description" => $description
                ]
            ];

            $payment_url = env('PAYMENT_URL') . '/' . $payment_id . '/refunds';

            $res = $client->request(
                'POST',
                $payment_url,
                $options
            );

            $response = (string) $res->getBody();

            $response = json_decode($response);

            $data = $response->refund;

            $existing_parent_refund = Refund::where('parent_payment_id', $data->autotransaction->parent_payment_id)->latest('created_at')->first();

            $refund = new Refund();

            $refund->refund_id = $data->refund_id;
            $refund->creditnote_id = $data->creditnote->creditnote_id;
            $refund->creditnote_number = $data->creditnote->creditnote_number;
            if ($existing_parent_refund) {
                $refund->balance_amount = $existing_parent_refund->balance_amount -  $data->creditnote->refund_amount;
            } else {
                $refund->balance_amount = round($invoice->invoice_items['price'], 2) -  $data->creditnote->refund_amount;
            }

            $refund->refund_amount = $data->creditnote->refund_amount;
            $refund->zoho_cust_id = $invoice->zoho_cust_id;
            $refund->date = $data->date;
            $refund->description = $data->description;
            $refund->status = $data->status;
            $refund->parent_payment_id = $data->autotransaction->parent_payment_id;
            $refund->status = $data->status;
            $refund->refund_mode = $data->refund_mode;
            $refund->gateway_transaction_id = $data->autotransaction->gateway_transaction_id;
            $refund->payment_method_id = $data->autotransaction->card_id;
            $refund->save();
            $creditnote_id = $data->creditnote->creditnote_id;
            $creditnote->retrieveCreditNote($creditnote_id, $invoice->invoice_number);
            return back()->with('success', 'The refund information has been saved.');
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);

            $response = json_decode($response);

            return back()->with('fail', $response->message);
        }
    }

    public function updateToggle(Request $request)
    {

        $budget_cap = BudgetCapSettings::where('partner_id', $request->partner_id)->first();

        $toggle_id = $request->toggle_id;

        $budget_cap->$toggle_id = $request->state;
        $budget_cap->save();
        return response()->json(['success' => true]);
    }
}
