<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\Email;
use App\Mail\PartnerSetup;
use App\Models\AccessToken;
use App\Models\AffiliateId;
use App\Models\Affiliates;
use App\Models\Leads;
use App\Models\Partner;
use App\Models\PartnerAddress;
use App\Models\PartnersAffiliates;
use App\Models\PartnerUsers;
use App\Models\Plans;
use App\Models\ProviderAvailabilityData;
use App\Models\ProviderData;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;

class LeadController extends Controller
{
    public function getLeads(Request $request)
    {
        $existing_partner = Partner::where('isp_advertiser_id', $request->advertiser_id)->first();

        if ($existing_partner) {
            return back()->with('fail', 'Advertiser Id already exists');
        }
        $query = Leads::query();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {

            $query->whereDate('leads.created_at', '>=', $startDate)
                ->whereDate('leads.created_at', '<=', $endDate);
        }

        if ($request->has('search')) {

            $search = $request->input('search');

            $query->where(function ($q) use ($search) {

                $q->where('company_name', 'LIKE', "%{$search}%")
                    ->orWhere('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

        $leads = $query->orderByDesc('id')->paginate($perPage);

        $affiliates = Affiliates::all();
        return view('admin.leads', compact('leads', 'affiliates'));
    }

    public function approveLead()
    {
        $id = Route::getCurrentRoute()->id;
        $lead = Leads::find($id);
        $affiliates = Affiliates::all();
        $values = [];
        foreach ($affiliates as $affiliate) {
            array_push($values, $affiliate->isp_affiliate_id . "(" . $affiliate->domain_name . ")");
        }
        $plans = Plans::all();
        return view('/admin/invite-partner', compact('lead', 'affiliates', 'plans', 'values'));
    }

    public function approveLeadOld(Request $request)
    {
        $lead = Leads::where('id', $request->lead_id)->first();
        try {

            $partner_url = env('PARTNER_URL');

            $access_token = $this->getAccessToken();

            if (!$access_token) {

                return back()->with('fail', 'Kindly Try Again');
            }


            if ($this->partnerExists($request->email)) {

                return back()->with('fail', 'Email already exists');
            }

            $response = $this->createPartnerInZoho($lead, $access_token, $partner_url, $request);

            if (!$response) {

                return back()->with('fail', 'Failed to create partner in Zoho');
            }

            $zoho_cust = $response->customer;

            $savedPartner = $this->savePartner($zoho_cust, $request, $lead);

            $partner = $savedPartner['partner'];



            if (!$partner) {

                return back()->with('fail', 'Failed to save partner data');
            }



            $lead->status = "Approved";
            $lead->save();


            $data = new ProviderData();
            $data->logo_image = $lead->company_info['logo_image'];
            $data->landing_page_url = $lead->company_info['landing_page_url'];
            $data->landing_page_url_spanish = $lead->company_info['landing_page_url_spanish'];
            $data->company_name = $lead->company_info['provider_company_name'];
            $data->zoho_cust_id = $partner->zoho_cust_id;
            $data->save();


            $providerAvailabilityData = new ProviderAvailabilityData();

            $providerAvailabilityData->file_size = $lead->availability_data['file_size'];
            $providerAvailabilityData->file_name = $lead->availability_data['file_name'];
            $providerAvailabilityData->zip_count = $lead->availability_data['zip_count'];
            $providerAvailabilityData->url = $lead->availability_data['url'];
            $providerAvailabilityData->zoho_cust_id = $partner->zoho_cust_id;
            $providerAvailabilityData->save();

            return redirect('/admin/leads')->with('success', 'Lead approved successfully');
        } catch (GuzzleException $e) {

            return redirect('/signup')->with('fail', $e->getMessage());
        }
    }

    private function getAccessToken()
    {

        $latestToken = AccessToken::latest('created_at')->first();

        return $latestToken ? $latestToken->access_token : null;
    }

    private function partnerExists($email)
    {
        return PartnerUsers::where('email', $email)->exists();
    }


    private function createPartnerInZoho($lead, $access_token, $partner_url, $request)
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
                    "display_name" => $lead->company_name,
                    "first_name" => $lead->first_name,
                    "last_name" => $lead->last_name,
                    "email" => $lead->email,
                    "company_name" => $lead->company_name,
                    "billing_address" => [
                        "attention" => $lead->first_name . " " . $lead->last_name,
                        "street" => $lead->street,
                        "city" => $lead->city,
                        "state" => $lead->state,
                        "zip" => $lead->zip_code,
                        "country" => "United States"
                    ],
                    "shipping_address" => [
                        "attention" =>  $lead->first_name . " " . $lead->last_name,
                        "street" => $lead->street,
                        "city" => $lead->city,
                        "state" =>  $lead->state,
                        "zip" => $lead->zip_code,
                        "country" => $lead->country
                    ],
                    "payment_terms" => 0,
                    "ach_supported" => true,
                    "payment_terms_label" => "Due on receipt",
                    "currency_code" => "USD",
                    "custom_fields" => [
                        [
                            "label" => "isp_affiliate_id",
                            "value" => "$request->affiliate_id",
                        ],
                        [
                            "label" => "isp_advertiser_id",
                            "value" => "$request->advertiser_id",
                        ],
                        [
                            "label" => "isp_tax_number",
                            "value" => $lead->tax_number
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


    private function savePartner($zoho_partner, $request, $lead)
    {
        $partner = new Partner();
        $partner->zoho_cust_id = $zoho_partner->customer_id;
        $partner->company_name = $zoho_partner->company_name;
        $partner->tax_number = $lead->tax_number;
        $partner->isp_advertiser_id = $request->advertiser_id;
        $partner->is_approved = true;
        $partner->save();

        $affiliateIds = $request->affiliate_id;

        $affiliateIdsArray = explode(',', $affiliateIds);

        $affiliateIdsFromTable = Affiliates::whereIn('isp_affiliate_id', $affiliateIdsArray)
            ->pluck('id');

        foreach ($affiliateIdsFromTable as $affiliateId) {
            $partner_affiliate = new PartnersAffiliates();
            $partner_affiliate->affiliate_id = $affiliateId;
            $partner_affiliate->partner_id = $partner->id;
            $partner_affiliate->save();
        }

        $unhashedPassword = Str::random(16);


        $partnerUser = new PartnerUsers();
        $partnerUser->zoho_cust_id = $zoho_partner->customer_id;
        $partnerUser->zoho_cpid = $zoho_partner->primary_contactperson_id;
        $partnerUser->first_name = $zoho_partner->first_name;
        $partnerUser->last_name = $zoho_partner->last_name;
        $partnerUser->email = $zoho_partner->email;
        $partnerUser->password = Hash::make($unhashedPassword);
        $partnerUser->phone_number = $lead->phone_number;
        $partnerUser->is_primary = true;
        $partnerUser->status = 'active';
        $partnerUser->invitation_status = "Invited";
        $partnerUser->save();

        if (!empty($zoho_partner->billing_address)) {

            $this->savePartnerAddress($zoho_partner, $partner->zoho_cust_id);
        }
        $this->sendMail($partnerUser, $partner, $unhashedPassword);
        return [
            'partner' => $partner,
            'partner_user' => $partnerUser,
        ];
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



    public function sendMail($partner_user, $partner, $unhashedPassword)
    {
        $app_url = env('APP_URL');
        $email = $partner_user->email;
        $name = $partner_user->first_name . ' ' . $partner_user->last_name;
        $password = $unhashedPassword;
        $company_name = $partner->company_name;

        Mail::to($email)->send(new PartnerSetup($app_url, $name, $password, $company_name));
    }


    public function rejectLead()
    {
        $id = Route::getCurrentRoute()->id;
        $lead = Leads::find($id);
        $lead->status = "Rejected";
        $lead->save();

        return back()->with('success', "Lead Rejected Successfully");
    }


    public function viewLeadOverview()
    {
        $id = Route::getCurrentRoute()->id;

        $lead = Leads::where('id', $id)->first();
        return view('admin/lead-view/overview', compact('lead'));
    }

    public function viewLeadProviderData()
    {
        $id = Route::getCurrentRoute()->id;

        $lead = Leads::where('id', $id)->first();
        $data = $lead->company_info;
        $url = null;
        if ($data) {
            $url = $this->generatePresignedUrl($data->logo_image);
        }
        $availability_data = $lead->availability_data;

        return view('admin/lead-view/provider-data', compact('lead', 'data', 'availability_data', 'url'));
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
}
