<?php

namespace App\Http\Controllers;

use App\Models\AccessToken;
use App\Models\Invoices;
use App\Models\Partner;
use App\Models\ProviderAvailabilityData;
use App\Models\ProviderData;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class InvoicesController extends Controller
{
    public function getInvoice(Request $request)

    {
        if (Session::has('loginPartner')) {
            try {

                $partnerId = Session::get('loginId');

                $query = Invoices::where('zoho_cust_id', $partnerId);

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
                            ->orWhereJsonContains('invoice_items->price', $search);
                    });
                }

                $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

                $invoices = $query->orderByDesc('invoice_number')->paginate($perPage);

                $showModal = false;
                $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();
                $availability_data = ProviderAvailabilityData::where('zoho_cust_id', Session::get('loginId'))->first();
                $company_info = ProviderData::where('zoho_cust_id', Session::get('loginId'))->first();

                // if ($availability_data === null || $company_info === null) {
                //     $showModal = true;
                // }
                return view('partner.invoices', compact('invoices', 'showModal', 'availability_data', 'company_info'));
            } catch (Exception $e) {

                return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
            }
        }
    }

    public function recordPayment(AccessToken $token, Request $request)
    {

        $token1 = AccessToken::latest('created_at')->first();

        if ($token1 !== null) {

            $access_token = $token1->access_token;
        } else {

            $token->getToken();

            $token1 = AccessToken::latest('created_at')->first();

            return back()->with('fail', 'Kindly Try Again');
        }
        $invoice =  Invoices::where('invoice_id', $request->invoice_id)->first();
        $partner = Partner::where('zoho_cust_id', $invoice->zoho_cust_id)->first();
        try {
            $client = new \GuzzleHttp\Client();

            $options = [
                'headers' => [
                    'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                    'content-type: application/json'
                ],
                'json' => [
                    "customer_id" => $invoice->zoho_cust_id,
                    "payment_mode" => $request->payment_mode,
                    "amount" => $request->amount,
                    "date" => $request->payment_date,
                    "reference_number" => $request->reference_number,

                    "invoices" => [
                        [
                            "invoice_id" => $request->invoice_id,
                            "amount_applied" => $request->amount,
                        ]

                    ],
                    "bank_charges" => $request->bank_charges
                ]
            ];

            $res = $client->request(
                'POST ',
                'https://www.zohoapis.com/billing/v1/payments',
                $options
            );

            $response = (string) $res->getBody();
            $response = json_decode($response);

            $payment = $response->payment;
            $invoice->payment_details =  [
                'payment_id' => $payment->payment_id,
                'payment_mode' => $payment->payment_mode,
                'reference_number' => $payment->reference_number,
                'payment_date' =>  $payment->date,
                'payment_amount' => $payment->amount

            ];
            $invoice->payment_made = $payment->amount;
            $invoice->status = 'paid';
            $invoice->save();
            $this->getOutstanding($partner, $access_token);
            return redirect('/admin/invoice')->with('success', 'Payment recorded successfully');
        } catch (GuzzleException $e) {

            $response = $e->getResponse()->getBody(true);
            $response = json_decode($response);

            if ($response->message === "You are not authorized to perform this operation") {

                $token->getToken();

                return back()->with('fail', 'Kindly Subscribe Again!');
            }
        }
    }

    public function getOutstanding($partner, $access_token)
    {
        $partner_url = env('PARTNER_URL');

        $client = new \GuzzleHttp\Client();

        $options = [
            'headers' => [
                'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
            ]
        ];

        $res = $client->request(
            'GET',
            $partner_url . '/' . $partner->zoho_cust_id,
            $options
        );

        $response = (string) $res->getBody();
        $response = json_decode($response);
        $customer = $response->customer;
        $partner->outstanding_invoices = $customer->outstanding;
        $partner->unused_credits = $customer->unused_credits;
        $partner->save();
    }

    public function termsConditions()
    {
        $showModal = false;
        return view('partner.terms_conditions', compact('showModal'));
    }
}
