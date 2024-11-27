<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\AccessToken;
use App\Models\Partner;
use App\Models\PartnersAffiliates;
use App\Models\Clicks;
use Carbon\Carbon;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Log;

class PendingInvoiceController extends Controller
{
    public function updatePendingInvoices()
    {
        Log::info("Started updating invoices...");

        // Fetch latest access token
        $token = AccessToken::latest('created_at')->first();
        if (!$token) {
            Log::error('No access token found.');
            return response()->json(['status' => 'error', 'message' => 'No access token found.'], 500);
        }

        $access_token = $token->access_token;
        $organizationId = env('ORGANIZATION_ID');

        try {
            // Fetch overdue and pending invoices
            $overdueInvoicesResponse = $this->fetchZohoInvoices('Status.OverDue');
            $pendingInvoicesResponse = $this->fetchZohoInvoices('Status.Pending');

            if (!$overdueInvoicesResponse || !$pendingInvoicesResponse) {
                Log::error("Failed to fetch overdue or pending invoices from Zoho.");
                return response()->json(['status' => 'error', 'message' => 'Failed to fetch invoices.'], 500);
            }

            // Merge overdue and pending invoices
            $invoices = array_merge($overdueInvoicesResponse['invoices'], $pendingInvoicesResponse['invoices']);
            Log::info("Successfully retrieved " . count($invoices) . " invoices.");

            foreach ($invoices as $invoice) {
                $invoiceId = $invoice['invoice_id'];
                $customerId = $invoice['customer_id'];
                $invoiceDate = $invoice['invoice_date'];

                // Retrieve partner and payment method details
                $partner = Partner::where('zoho_cust_id', $customerId)->first();
                if (!$partner) {
                    Log::error("No partner found for customer ID {$customerId}.");
                    continue;
                }

                $paymentMethod = $this->fetchCardAndBankAccountData($customerId);

                if (isset($paymentMethod['error'])) {

                    Log::error("Payment method error: " . json_encode($paymentMethod));

                    continue;
                }

                $partnerId = $partner->id;

                $partnersAffiliates = PartnersAffiliates::where('partner_id', $partnerId)->pluck('id');
                if ($partnersAffiliates->isEmpty()) {
                    Log::error("No affiliates found for partner ID {$partnerId}.");
                    continue;
                }

                $subscriptions = DB::table('subscriptions')->where('zoho_cust_id', $customerId)->get();
                if ($subscriptions->isEmpty()) {
                    Log::error("No subscriptions found for customer ID {$customerId}.");
                    continue;
                }

                $customerSubscribedPlanId = $subscriptions->first()->plan_id;
                $plans = DB::table('plans')->where('plan_id', $customerSubscribedPlanId)->get();
                if ($plans->isEmpty()) {
                    Log::error("No plans found for plan ID {$customerSubscribedPlanId}.");
                    continue;
                }

                $plan = $plans->first();
                $planCode = $plan->plan_code;
                $planPrice = $plan->price;
                $planIntervalUnit = $plan->interval_unit;

                if (strpos($planCode, 'cpc') !== false) {

                    Log::info("Processing 'cpc' plan for customer ID {$customerId}.");

                    $dateRange = $this->getDateRange($planIntervalUnit, $invoiceDate);
                    $startDate = $dateRange['start_date'];
                    $endDate = $dateRange['end_date'];

                    Log::info("Date range for plan: Start Date = {$startDate}, End Date = {$endDate}.");

                    // Calculate click usage
                    $clickDetails = 'Click Usage ';
                    $totalClicks = 0;
                    $currentDate = Carbon::parse($startDate);
                    while ($currentDate->lte(Carbon::parse($endDate))) {
                        $dayClickCount = Clicks::whereIn('partners_affiliates_id', $partnersAffiliates)
                            ->whereDate('click_ts', $currentDate->toDateString())
                            ->count();
                        $totalClicks += $dayClickCount;
                        $clickDetails .= $dayClickCount . ',';
                        $currentDate->addDay();
                    }

                    $updateResponse = $this->updateInvoiceLineItems($invoiceId, $planCode, $planPrice, $startDate, $endDate, [$totalClicks, $clickDetails], $customerId);
                    
                    if ($updateResponse && $updateResponse->successful()) {

                        Log::info("Line item added to Invoice ID {$invoiceId} successfully.");

                        $this->chargeInvoice($invoiceId, $paymentMethod); 

                    } else {

                        Log::error("Failed to update Invoice ID {$invoiceId}. Response: " . optional($updateResponse)->body());
                    }
                } else {
                    Log::info("Plan code for customer ID {$customerId} does not contain 'cpc'.");
                }
            }

        } catch (\Exception $e) {
            Log::error("Zoho API request failed: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Zoho API request failed.'], 500);
        }

        return response()->json(['status' => 'success']);
    }

    private function fetchZohoInvoices($filterBy)
    {
        $token = AccessToken::latest('created_at')->first();
        if (!$token) {
            Log::error('No access token found.');
            return null;
        }

        $access_token = $token->access_token;
        $organizationId = env('ORGANIZATION_ID');
        $url = "https://www.zohoapis.com/billing/v1/invoices?filter_by=" . urlencode($filterBy);

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $access_token,
            'X-com-zoho-subscriptions-organizationid' => $organizationId
        ])->get($url);

        return $response->successful() ? $response->json() : null;
    }

    private function updateInvoiceLineItems($invoiceId, $planCode, $planPrice, $startDate, $endDate, $clickData, $customerId)
    {
        $token = AccessToken::latest('created_at')->first();
        if (!$token) {
            Log::error('No access token found.');
            return null;
        }

        $access_token = $token->access_token;
        $totalClicks = $clickData[0];
        $clickDetails = $clickData[1];

        $data = [
            'customer_id' => $customerId,
            'invoice_items' => [
                [
                    'code' => $planCode,
                    'description' => "Click Usage charges from {$startDate} to {$endDate}",
                    'price' => $planPrice,
                    'quantity' => $totalClicks,
                    'item_total' => $planPrice * $totalClicks,
                ]
            ],
            'notes' => $clickDetails,
            'reason' => "Updating line items to reflect correct click usage charges from {$startDate} to {$endDate}",
        ];

        $updateResponse = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $access_token,
            'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
            'Content-Type' => 'application/json',
        ])->put("https://www.zohoapis.com/billing/v1/invoices/{$invoiceId}", $data);

        return $updateResponse->successful() ? $updateResponse : null;
    }

    public function fetchCardAndBankAccountData($customerId)
    {
        $cardData = $this->fetchCustomerCardData($customerId);

        $bankAccountData = $this->fetchBankAccountData($customerId);

        return [
            'card_data' => $cardData,
            'bank_account_data' => $bankAccountData
        ];
    }

    private function fetchCustomerCardData($customerId)
    {
        $token = AccessToken::latest('created_at')->first();
        if (!$token) {
            Log::error('No access token found.');
            return ['error' => 'No access token found.'];
        }

        $access_token = $token->access_token;
        $organizationId = env('ORGANIZATION_ID');
        $url = "https://www.zohoapis.com/billing/v1/customers/{$customerId}/cards";

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $access_token,
            'X-com-zoho-subscriptions-organizationid' => $organizationId,
        ])->get($url);

        if ($response->successful()) {
            return $response->json();
        } else {
            Log::error("Failed to fetch card data for customer ID {$customerId}. Response: " . $response->body());
            return ['error' => 'Failed to fetch card data.'];
        }
    }

    private function fetchBankAccountData($customerId)
    {
        $token = AccessToken::latest('created_at')->first();
        if (!$token) {
            Log::error('No access token found.');
            return ['error' => 'No access token found.'];
        }

        $access_token = $token->access_token;
        $organizationId = env('ORGANIZATION_ID');
        $url = "https://www.zohoapis.com/billing/v1/customers/{$customerId}/bank_accounts";

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $access_token,
            'X-com-zoho-subscriptions-organizationid' => $organizationId,
        ])->get($url);

        if ($response->successful()) {
            return $response->json();
        } else {
            Log::error("Failed to fetch bank account data for customer ID {$customerId}. Response: " . $response->body());
            return ['error' => 'Failed to fetch bank account data.'];
        }
    }

    public function chargeInvoice($invoiceId, $paymentDetails)
    {
        $paymentMethodId = '';

        if (isset($paymentDetails['card_data']['cards']) && count($paymentDetails['card_data']['cards']) > 0) {

            $paymentMethodId = $paymentDetails['card_data']['cards'][0]['card_id'];

        } elseif (isset($paymentDetails['bank_account_data']['bank_accounts']) && count($paymentDetails['bank_account_data']['bank_accounts']) > 0) {
            
            $paymentMethodId = $paymentDetails['bank_account_data']['bank_accounts'][0]['bank_account_id'];
        }

        if (!$paymentMethodId) {

            Log::error("No payment method found for invoice ID {$invoiceId}. Skipping charge.");
            return;
        }

        $token = AccessToken::latest('created_at')->first();
        if (!$token) {
            Log::error("No access token found to charge invoice ID {$invoiceId}.");
            return;
        }

        $access_token = $token->access_token;
        
        $url = "https://www.zohoapis.com/billing/v1/invoices/{$invoiceId}/collect";

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $access_token,
            'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
        ])->post($url, [
            'payment_method_id' => $paymentMethodId
        ]);

        if (!$response->successful()) {
            Log::error("Failed to charge invoice ID {$invoiceId}. Response: " . $response->body());
        }
    }
        
    private function getDateRange($intervalUnit, $invoiceDate)
    {
        $end = Carbon::parse($invoiceDate)->subDay(); 

        switch ($intervalUnit) {
            
            case 'months':
                $start = $end->copy()->subMonth(); 
                break;
            case 'years':
                $start = $end->copy()->subYear(); 
                break;
            case 'weeks':
                $start = $end->copy()->subDays(6);
                break;

            default:
                $start = $end->copy();
        }

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ];
    }

    
}
