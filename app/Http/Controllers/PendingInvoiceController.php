<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\AccessToken;
use App\Models\Partner;
use App\Models\PartnersAffiliates;
use App\Models\Click;
use Carbon\Carbon;

class PendingInvoiceController extends Controller
{
    public function updatePendingInvoices()
    {
        
        $token = AccessToken::latest('created_at')->first();

        if (!$token) {
            \Log::error('No access token found.');
            return response()->json(['status' => 'error', 'message' => 'No access token found.'], 500);
        }

        $access_token = $token->access_token;

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $access_token,
            'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
            'Content-Type' => 'application/json',
        ])->get('https://www.zohoapis.com/billing/v1/invoices', [
            'filter_by' => 'Status.Pending',
        ]);

        if ($response->successful()) {
            $invoices = $response->json()['invoices'];

            foreach ($invoices as $invoice) {

                $invoiceId = $invoice['invoice_id'];
                
                $customerId = $invoice['customer_id'];

                $partner = Partner::where('customer_id', $customerId)->first();

                if (!$partner) {

                    \Log::error("Partner not found for customer ID {$customerId}.");
                    continue;
                }

                $partnerId = $partner->id;

                $partnersAffiliates = PartnersAffiliates::where('partner_id', $partnerId)->pluck('id');

                if ($partnersAffiliates->isEmpty()) {

                    \Log::error("No partners affiliates found for partner ID {$partnerId}.");
                    continue;
                }

                $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();

                $clickCount = Click::whereIn('partners_affiliate_id', $partnersAffiliates)
                                   ->whereBetween('click_ts', [$startOfLastMonth, $endOfLastMonth])
                                   ->count();

                $lineItemData = [
                    'line_items' => [
                        [
                            'name' => 'Clicks Charges',
                            'quantity' => $clickCount,
                            'unit' => 'qty',
                            'rate' => 3, 
                        ]
                    ]
                ];

                $updateResponse = Http::withHeaders([
                    'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),

                    'Content-Type' => 'application/json',
                    
                ])->post("https://www.zohoapis.com/billing/v1/invoices/{$invoiceId}/lineitems", $lineItemData);

                if ($updateResponse->successful()) {
                    \Log::info("Line item added to Invoice ID {$invoiceId} successfully.");
                } else {
                    \Log::error("Failed to add line item to Invoice ID {$invoiceId}. Response: " . $updateResponse->body());
                }
            }

        } else {

            \Log::error("Failed to fetch pending invoices. Response: " . $response->body());
        }

        return response()->json(['status' => 'success']);
    }
}
