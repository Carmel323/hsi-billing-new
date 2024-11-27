<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;
use App\Models\AccessToken;
use App\Models\HostedPageId;
use App\Models\Plans;
use App\Models\Subscriptions;
use App\Models\PaymentMethod;
use App\Models\CreditNotes;
use App\Models\Partner;
use App\Models\Invoices;
use App\Models\Support;
use App\Models\Terms;
use Illuminate\Support\Facades\Log;



class HostedPageController extends Controller
{
    public function thankyouCreate(Request $request, CreditNotes $creditnote)
    {

        $hostedpage_id = $this->extractHostedPageIdFromUrl($request);

        if (!$hostedpage_id) {

            if (Session::has('loginPartner')) {

                return redirect('/')->with('fail', 'Hosted page ID is required.');
            } else {

                return redirect('/admin/support')->with('fail', 'Hosted page ID is required.');
            }
        }


        $response = $this->fetchHostedPageData($hostedpage_id);

        $subscription = $this->handleSubscription($response);

        $invoice = $this->handleInvoice($response, $creditnote);

        $paymentmethod = $this->handlePaymentMethod($response);

        $terms = $this->handleTerms($response);

        $partner = $this->handlePartner($response);

        $invoice_link = $response->data->invoice->invoice_url;

        $status = $response->status;

        $invoice_status = $response->data->invoice->status;

        $planName = $response->data->subscription->plan->name;

        $planPrice = $response->data->subscription->plan->price;

        $showModal = false;
        if ($response->action === "one_time_addon") {

            return redirect('/subscription')->with('success', 'Addon Added successfully');
        } else {

            return view('partner.thankyou', compact('status', 'invoice_status', 'subscription', 'invoice', 'invoice_link', 'planName', 'planPrice', 'showModal'));
        }
    }


    public function thankyouUpdate(Request $request, CreditNotes $creditnote)
    {
        $hostedpage_id = $this->extractHostedPageIdFromUrl($request);

        if (!$hostedpage_id) {

            if (Session::has('loginPartner')) {

                return redirect('/')->with('fail', 'Hosted page ID is required.');
            } else {

                return redirect('/admin/support')->with('fail', 'Hosted page ID is required.');
            }
        }

        $response = $this->fetchHostedPageData($hostedpage_id);

        $subscription = $this->handleSubscription($response);

        $invoice = $this->handleInvoice($response, $creditnote);

        $partner = $this->handlePartner($response);

        $paymentmethod = $this->handlePaymentMethod($response);

        $terms = $this->handleTerms($response);

        $invoice_link = $response->data->invoice->invoice_url;

        $status = $response->status;

        $invoice_status = $response->data->invoice->status;

        $planName = $response->data->subscription->plan->name;

        $zoho_cust_id = $response->data->subscription->customer->customer_id;

        $planPrice = $response->data->subscription->plan->price;

        $showModal = false;

        if ($response->action === "update_card") {
            if (Session::has('loginAdmin')) {
                $partner = Partner::where('zoho_cust_id', $zoho_cust_id)->first();
                return redirect('/admin/view-partner/' . $partner->id)->with('success', 'Card details updated successfully');
            } elseif (Session::has('loginPartner')) {
                return redirect('/subscription')->with('success', 'Payment Method Updated Successfully');
            }
        } else if ($response->action === "one_time_addon") {

            return redirect('/subscription')->with('success', 'Addon Added successfully');
        } else {
            return view('partner.thankyou', compact('status', 'invoice_status', 'subscription', 'invoice', 'invoice_link', 'planName', 'planPrice', 'showModal'));
        }
    }

    public function thankyouDowngrade(Request $request, CreditNotes $creditnote)
    {
        $hostedpage_id = $this->extractHostedPageIdFromUrl($request);

        if (!$hostedpage_id) {
            return redirect('/admin/support')->with('fail', 'Hosted page ID is required.');
        }


        $response = $this->fetchHostedPageData($hostedpage_id);


        $this->handleSubscription($response);

        $this->handleInvoice($response, $creditnote);

        $this->handlePartner($response);

        $this->handlePaymentMethod($response);

        $this->handleTerms($response);

        $zoho_cust_id = $response->data->subscription->customer->customer_id;

        $support = Support::where('request_type', '=', 'Downgrade')->where('status', '=', 'open')
            ->where('zoho_cust_id', $zoho_cust_id)->first();

        if ($support) {
            $support->status = 'Completed';
            $support->comments = 'Unable to revoke';
            $support->save();

            return redirect('/admin/support')->with('success', 'Subscripition downgraded successfully');
        }
    }

    public function addPaymentMethod(Request $request)
    {
        $hostedpage_id = $this->extractHostedPageIdFromUrl($request);

        if (!$hostedpage_id) {
            return redirect('/admin/support')->with('fail', 'Hosted page ID is required.');
        }

        $response = $this->fetchHostedPageData($hostedpage_id);


        $data = $response->data;

        $paymentMethodId = $data->payment_method->payment_method_id;

        $partnerId = $data->payment_method->customer->customer_id;


        $paymentMethod = new PaymentMethod();

        $paymentMethod->type = $data->payment_method->type;

        $paymentMethod->payment_method_id = $paymentMethodId;

        $paymentMethod->zoho_cust_id = $partnerId;

        $paymentMethod->last_four_digits = $data->payment_method->type === "card" ? $data->payment_method->last_four_digits : $data->payment_method->account_number;

        $paymentMethod->payment_gateway = $data->payment_method->payment_gateway;

        $paymentMethod->expiry_month = $data->payment_method->expiry_month ?? '';

        $paymentMethod->expiry_year = $data->payment_method->expiry_year ?? '';

        $paymentMethod->save();

        return redirect('/profile')->with('success', 'Payment method added successfully');
    }

    public function extractHostedPageIdFromUrl($request)
    {
        $queryParams = $request->query();

        if (isset($queryParams['hostedpage_id'])) {

            $hostedpage_id = $queryParams['hostedpage_id'];

            $hostedpage_id = strtok($hostedpage_id, '?');

            return $hostedpage_id;
        }

        return null;
    }

    private function fetchHostedPageData($hostedpage_id)
    {

        $hostedpage_url = env('HOSTEDPAGE_URL');

        $token = AccessToken::latest('created_at')->first();

        $access_token = $token->access_token;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $hostedpage_url . $hostedpage_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Zoho-oauthtoken ' . $access_token,
                'X-com-zoho-subscriptions-organizationid: ' . env('ORGANIZATION_ID')
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }


    private function handleSubscription($response)
    {
        $data = $response->data;

        $action = $response->action;

        try {

            $plan = Plans::where('plan_code', '=', $data->subscription->plan->plan_code)->first();

            $subscription = Subscriptions::where('subscription_id', $data->subscription->subscription_id)->first();

            $isNewSubscription = false;

            if (!$subscription) {

                $subscription = new Subscriptions();

                $isNewSubscription = true;
            }

            $plan = Plans::where('plan_code', '=', $data->subscription->plan->plan_code)->first();

            $subscription->subscription_id = $data->subscription->subscription_id;

            $subscription->subscription_number = $data->subscription->subscription_number;

            $subscription->status = $data->subscription->status;

            $subscription->start_date = $data->subscription->current_term_starts_at;

            $subscription->next_billing_at = $data->subscription->next_billing_at;

            $subscription->plan_id = $plan->plan_id;

            $subscription->zoho_cust_id = $data->subscription->customer->customer_id;

            $subscription->invoice_id = $data->invoice->invoice_id;

            if (isset($data->subscription->card->card_id)) {

                $subscription->payment_method_id = $data->subscription->card->card_id;
            }
            if (isset($data->subscription->bank_account->account_id)) {

                $subscription->payment_method_id = $data->subscription->bank_account->account_id;
            }





            if ($action === "one_time_addon") {

                $subscription->addon = $data->invoice->invoice_items[0]->code;

                $subscription->save();
            } else {

                $subscription->addon = null;

                $subscription->save();
            }

            return $subscription;
        } catch (\Exception $e) {

            Log::error('Error occurred while handling subscription: ' . $e->getMessage());

            return null;
        }
    }



    private function handleInvoice($response, $creditnote)
    {

        $data = $response->data;

        $payment_method = 'others';

        if (isset($data->subscription->bank_account)) {

            $payment_method = 'bank_account';
        }

        if (isset($data->subscription->card)) {

            $payment_method = 'card';
        }



        $action = $response->action;

        $plan = Plans::where('plan_code', $data->subscription->plan->plan_code)->latest('created_at')->firstOrFail();

        $invoice = Invoices::where('invoice_id', $data->invoice->invoice_id)->first();

        if (!$invoice) {

            $invoice = new Invoices();

            $invoice->invoice_id = $data->invoice->invoice_id;

            $invoice->invoice_number = $data->invoice->number;
        }


        $invoice->invoice_date = $data->invoice->invoice_date;
        $invoice->credits_applied = $data->invoice->credits_applied;
        $invoice->discount = $data->invoice->invoice_items[0]->discount_amount;
        $invoice->payment_method = $payment_method;
        $invoice->payment_made = $data->invoice->payment_made;
        $invoice->invoice_link = $data->invoice->invoice_url;
        $invoice->zoho_cust_id = $data->subscription->customer->customer_id;
        $invoice->balance = $data->invoice->balance;
        $invoice->status = $data->invoice->status;
        $invoice->subscription_id = [
            "subscription_id" => $data->subscription->subscription_id,
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


        if ($data->invoice->credits) {

            $this->handleCreditNotes($response, $creditnote, $data->invoice->number);
        }

        return $invoice;
    }

    private function handleCreditNotes($response, $creditnote, $invoiceNumber)
    {
        $data = $response->data;

        $action = $response->action;

        if ($data->invoice->credits) {

            foreach ($data->invoice->credits as $creditData) {

                $creditnote->retrieveCreditNote($creditData->creditnote_id, $invoiceNumber);
            }
        }
    }

    public function handlePaymentMethod($response)
    {
        $data = $response->data;

        $action = $response->action;

        $payment_method_type = '';

        if (isset($data->subscription->card)) {


            $this->handleCard($data);
        }

        if (isset($data->subscription->bank_account)) {

            $this->handleBankAccount($data);
        }
    }

    private function handleCard($data)
    {

        $paymentMethodId = $data->subscription->card->card_id;

        $partnerId = $data->subscription->customer->customer_id;

        $existingPay = PaymentMethod::where('payment_method_id', $paymentMethodId)
            ->where('zoho_cust_id', $partnerId)
            ->first();

        if ($existingPay) {

            $existingPay->last_four_digits = $data->subscription->card->last_four_digits;

            $existingPay->payment_gateway = $data->subscription->card->payment_gateway;

            $existingPay->expiry_month = $data->subscription->card->expiry_month;

            $existingPay->expiry_year = $data->subscription->card->expiry_year;

            $existingPay->save();
        } else {

            $paymentMethod = new PaymentMethod();

            $paymentMethod->type = 'card';

            $paymentMethod->payment_method_id = $paymentMethodId;

            $paymentMethod->zoho_cust_id = $partnerId;

            $paymentMethod->last_four_digits = $data->subscription->card->last_four_digits;

            $paymentMethod->payment_gateway = $data->subscription->card->payment_gateway;

            $paymentMethod->expiry_month = $data->subscription->card->expiry_month;

            $paymentMethod->expiry_year = $data->subscription->card->expiry_year;

            $paymentMethod->save();
        }
    }

    private function handleBankAccount($data)
    {

        $bankacct = $data->subscription->bank_account;

        $paymentMethodId = $bankacct->account_id;

        $partnerId = $data->subscription->customer->customer_id;

        $existingPay = PaymentMethod::where('payment_method_id', $paymentMethodId)
            ->where('zoho_cust_id', $partnerId)
            ->first();

        if ($existingPay) {

            $existingPay->last_four_digits = $bankacct->last_four_digits;

            $existingPay->payment_gateway = $bankacct->payment_gateway;

            $existingPay->expiry_month = '';

            $existingPay->expiry_year = '';

            $existingPay->save();
        } else {

            $paymentMethod = new PaymentMethod();

            $paymentMethod->type = 'bank_account';

            $paymentMethod->payment_method_id = $paymentMethodId;

            $paymentMethod->zoho_cust_id = $partnerId;

            $paymentMethod->last_four_digits = $bankacct->last_four_digits;

            $paymentMethod->payment_gateway = $bankacct->payment_gateway;

            $paymentMethod->expiry_month = '';

            $paymentMethod->expiry_year = '';

            $paymentMethod->save();
        }
    }


    public function get_client_ip()
    {

        $ipaddress = '';

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {

            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {

            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];

            $ipaddress = explode(',', $ipaddress)[0];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {

            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {

            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {

            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {

            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {

            $ipaddress = 'UNKNOWN';
        }

        if (filter_var($ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $ipaddress;
        }

        return 'UNKNOWN';
    }


    private function handleTerms($response)
    {



        $data = $response->data;

        $action = $response->action;

        $term = new Terms();

        $term->zoho_cust_id = $data->subscription->customer->customer_id;

        $userId = Session::get('userId');
        if ($userId) {
            $term->zoho_cpid = $userId;
            $term->consent = true;
        } else {
            $term->zoho_cpid = 'admin';

            $term->consent = false;
        }

        $term->subscription_number = $data->subscription->subscription_number;

        $term->ip_address = $this->get_client_ip();

        $term->browser_agent = $_SERVER['HTTP_USER_AGENT'];




        if ($action === "one_time_addon") {

            $term->plan_name = $data->invoice->invoice_items[0]->name;
            $term->amount = $data->invoice->invoice_items[0]->price;
        } else {
            $term->plan_name = $data->subscription->plan->name;
            $term->amount = $data->subscription->plan->price;
        }


        $term->save();
    }

    private function handlePartner($response)
    {
        $data = $response->data;

        $zoho_cust_id = $data->subscription->customer->customer_id;

        $partner = Partner::where('zoho_cust_id', $zoho_cust_id)->first();

        $plan = Plans::where('plan_code', '=', $data->subscription->plan->plan_code)->first();
        $is_enterprise_plan = stripos($plan->plan_code, 'enterprise') !== false;

        $partner_response = $this->fetchPartner($zoho_cust_id);


        if ($is_enterprise_plan) {
            $selected_partner_plans = json_decode($partner->selected_plans);
            $selected_partner_plans[] = $plan->plan_id;
            $partner->selected_plans = json_encode($selected_partner_plans);
            $partner->save();
        }

        $partner->outstanding_invoices = $partner_response->customer->outstanding_receivable_amount;
        $partner->unused_credits = $partner_response->customer->unused_credits;
        $partner->save();
    }

    private function fetchPartner($zoho_cust_id)
    {
        $partner_url = env('PARTNER_URL');

        $token = AccessToken::latest('created_at')->first();

        $access_token = $token->access_token;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $partner_url . "/" . $zoho_cust_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Zoho-oauthtoken ' . $access_token,
                'X-com-zoho-subscriptions-organizationid: ' . env('ORGANIZATION_ID')
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }

    // public function retrieveCreditNote($creditnote_id)
    // {
    //     $creditnote_url = env('CREDITNOTES_URL');
    //     $token1 = AccessToken::latest('created_at')->first();
    //     $access_token = $token1->access_token;
    //     $client = new \GuzzleHttp\Client();

    //     $options = [
    //         'headers' => [
    //             'Authorization' => ('Zoho-oauthtoken ' . $access_token),
    //             'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
    //         ]
    //     ];

    //     $res = $client->request(
    //         'GET',
    //         $creditnote_url . $creditnote_id,
    //         $options
    //     );

    //     $response = (string) $res->getBody();
    //     $response = json_decode($response);
    //     $data = $response->creditnote;



    //     $creditNote = CreditNotes::where('creditnote_id', $creditnote_id)->first();

    //     if ($creditNote) {

    //         $invoices = $data->invoices;
    //         $credit_invoice_number = $invoices[0]->invoice_number;

    //         for ($i = 1; $i < count($invoices); $i++) {

    //             $credit_invoice_number = $credit_invoice_number . ',' . $invoices[$i]->invoice_number;
    //         }
    //         $creditNote->invoice_number = $credit_invoice_number;
    //         $creditNote->credited_date = $data->date;
    //         $creditNote->credited_amount = $data->total;
    //         $creditNote->balance = $data->balance;
    //         $creditNote->status = $data->status;
    //         $creditNote->save();
    //     } else {

    //         $creditNote = new CreditNotes();
    //         $creditNote->creditnote_id = $data->creditnote_id;
    //         $creditNote->creditnote_number = $data->creditnote_number;
    //         $creditNote->credited_date = $data->date;
    //         $creditNote->invoice_number = $data->invoices[0]->invoice_number;
    //         $creditNote->credited_amount = $data->total;
    //         $creditNote->balance = $data->balance;
    //         $creditNote->status = $data->status;
    //         $creditNote->zoho_cust_id = $data->customer_id;
    //         $creditNote->save();
    //     }
    // }
}
