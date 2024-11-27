<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Subscriptions;
use App\Models\Invoices;
use App\Models\Partner;
use App\Models\CreditNotes;
use App\Models\PartnerAddress;
use App\Models\PaymentMethod;
use App\Models\Refund;
use App\Models\SubscriptionHistory;
use App\Models\ProviderAvailabilityData;

class WebhookController extends Controller
{
    public function updateAOAStatus(Request $request)
    {
        $data = $request->all();
        $status = $data['status'];
        $fileId = $data['fileId'];
        $message = $data['message'] ?? null;

        $file = ProviderAvailabilityData::where('id', $fileId)->first();
        if($file) {
            if(!in_array($status, ['pending', 'approved', 'rejected'])) {
                return response()->json(['status' => 'error', 'message' => 'Invalid status'], 400);
            }
            $file->status = $status;
            $file->message = $message;
            $file->save();
            return response()->json(['status' => 'success', 'message' => 'File status updated'], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'File not found'], 404);
        }
    }

    public function handleSubscription(Request $request)
    {
        $data = $request->json()->all();
        $eventType = $data['event_type'] ?? null;

        if (in_array($eventType, [
            'subscription_created',
            'subscription_renewed',
            'subscription_upgraded',
            'subscription_downgraded',
            'subscription_unpaid',
            'subscription_cancelled',
            'subscription_reactivated',
            'subscription_cancellation_scheduled',
            'subscription_scheduled_cancellation_removed',
            'subscription_activation'
        ])) {

            $customer = $data['data']['subscription']['customer'];

            $partner = Partner::where('zoho_cust_id', $customer['customer_id'])->first();

            if ($partner) {

                $subscriptionData = $data['data']['subscription'];

                $subscription = Subscriptions::where([
                    ['subscription_id', '=', $subscriptionData['subscription_id']],
                    ['zoho_cust_id', '=', $customer['customer_id']]
                ])->first();

                if (!$subscription) {

                    $subscription = new Subscriptions();

                    $subscription->subscription_id = $subscriptionData['subscription_id'];

                    $subscription->subscription_number = $subscriptionData['subscription_number'];

                    $subscription->zoho_cust_id = $subscriptionData['customer']['customer_id'];

                    Log::info("Created new subscription: " . $subscriptionData['subscription_id']);
                } else {

                    Log::info("Updated subscription: " . $subscriptionData['subscription_id']);
                }

                $subscription->start_date = $subscriptionData['current_term_starts_at'];
                $subscription->next_billing_at = $subscriptionData['next_billing_at'];
                $subscription->plan_id = $subscriptionData['plan']['plan_id'];
                $subscription->invoice_id = $subscriptionData['child_invoice_id'] ?? "no invoice found";
                $subscription->payment_method_id = $subscriptionData['card']['card_id'] ?? $subscriptionData['bank_account']['account_id'] ?? null;

                $addon = $subscriptionData['addons'][0]['addon_code'] ?? null;

                $subscription->addon = $addon;

                // if(!empty($addon) && !in_array($eventType, ['subscription_renewed', 'subscription_upgraded', 'subscription_downgraded'])) {


                // }

                // if (in_array($eventType, ['subscription_renewed', 'subscription_upgraded', 'subscription_downgraded'])) {
                //     $subscription->addon = null;
                // }

                $subscription->status = $subscriptionData['status'];
                $subscription->updated_at = now();
                $subscription->save();

                $this->storeSubscriptionHistory($subscriptionData, $data);
                $this->storePartnerData($subscriptionData);
                $this->storePaymentData($subscriptionData);

                return response()->json(['status' => 'success', 'message' => 'Webhook processed'], 200);
            } else {
                return response()->json(['status' => 'success', 'message' => 'Customer not found'], 200);
            }
        } else {
            return response()->json(['status' => 'ignored', 'message' => 'Event not handled'], 200);
        }
    }

    protected function storeSubscriptionHistory($subscriptionData, $eventData)
    {
        $subscriptionHistory = new SubscriptionHistory();
        $subscriptionHistory->subscription_id = $subscriptionData['subscription_id'];
        $subscriptionHistory->zoho_cust_id = $subscriptionData['customer']['customer_id'];
        $subscriptionHistory->timestamp = now();
        $subscriptionHistory->event_details = $eventData;
        $subscriptionHistory->event_type = $eventData['event_type'];
        $subscriptionHistory->save();
    }

    private function storePartnerData($data)
    {
        $customer = $data['customer'];

        $partner = Partner::where('zoho_cust_id', $customer['customer_id'])->first();

        if (!$partner) {

            $partner = new Partner();

            $partner->zoho_cust_id = $customer['customer_id'];

            $partner->email = $customer['email'];

            $partner->invitation = 'Invited';


            Log::info("Created new partner: " . $customer['customer_id']);
        } else {

            Log::info("Updated partner: " . $customer['customer_id']);
        }

        $partner->company_name = $customer['company_name'];

        $partner->updated_at = now();

        $partner->save();

        $this->storePartnerAddress($customer);
    }

    private function storePartnerAddress($customer)
    {
        $address = PartnerAddress::where('zoho_cust_id', $customer['customer_id'])->first();

        if (!$address) {

            $address = new PartnerAddress();
        } else {

            Log::info("Updated partner address: " . $customer['customer_id']);
        }

        $address->zoho_cust_id = $customer['customer_id'];

        $address->country = $customer['billing_address']['country'];

        $address->street = $customer['billing_address']['street'];

        $address->city = $customer['billing_address']['city'];

        $address->state = $customer['billing_address']['state'];

        $address->zip_code = $customer['billing_address']['zip'];

        $address->updated_at = now();

        $address->save();
    }

    private function storePaymentData($data)
    {
        $paymentMethodId = '';

        $paymentmethod_type = '';

        if (isset($data['card'])) {

            $paymentmethodData = $data['card'];

            $paymentMethodId = $paymentmethodData['card_id'];

            $paymentmethod_type = 'card';
        } else {

            $paymentmethodData = $data['bank_account'];

            $paymentMethodId = $paymentmethodData['account_id'];

            $paymentmethod_type = 'bank_account';
        }

        $zoho_cust_id = $data['customer']['customer_id'];

        $paymentmethod = PaymentMethod::where('payment_method_id', $paymentMethodId)->first();

        if (!$paymentmethod) {

            $paymentmethod = new PaymentMethod();

            Log::info("Created new card data: " . $paymentMethodId);

            $paymentmethod->payment_method_id = $paymentMethodId;

            $paymentmethod->zoho_cust_id = $zoho_cust_id;

            $paymentmethod->created_at = now();
        } else {

            Log::info("Updated card data: " . $paymentMethodId);
        }

        $paymentmethod->type = $paymentmethod_type;

        $paymentmethod->last_four_digits = $paymentmethodData['last_four_digits'];

        $paymentmethod->payment_gateway = $paymentmethodData['payment_gateway'];

        $paymentmethod->expiry_month = isset($paymentmethodData['expiry_month']) ? $paymentmethodData['expiry_month'] : '';

        $paymentmethod->expiry_year = isset($paymentmethodData['expiry_year']) ? $paymentmethodData['expiry_year'] : '';
        $paymentmethod->updated_at = now();

        $paymentmethod->save();
    }

    public function handleInvoice(Request $request)
    {
        $data = $request->json()->all();

        if (!$data) {

            return response()->json(['status' => 'error', 'message' => 'Invalid JSON data'], 400);
        }

        $eventType = $data['event_type'] ?? null;

        if (in_array($eventType, [

            'invoice_notification',
            'invoice_updated'

        ])) {

            $invoiceData = $data['data']['invoice'] ?? null;

            foreach ($invoiceData['invoice_items'] as $item) {

                $code = $item['code'];

                $substring = "add-on";

                if (stripos($code, $substring) !== false) {

                    $subscription_id = $invoiceData['subscriptions'][0]['subscription_id'] ?? null;

                    $zoho_cust_id = $invoiceData['customer_id'] ?? null;

                    $subscription = Subscriptions::where('zoho_cust_id', $zoho_cust_id)
                        ->where('subscription_id', $subscription_id)
                        ->first();

                    if ($subscription) {

                        $subscription->addon = $code;

                        $subscription->save();
                    }
                }
            }


            $payment_method = 'others';


            if (isset($invoiceData['ach_payment_initiated'])) {

                if ($invoiceData['ach_payment_initiated'] === true) {

                    $payment_method = 'bank_account';
                } elseif ($invoiceData['ach_payment_initiated'] === false) {

                    $payment_method = 'card';
                }
            }

            if (!$invoiceData) {

                return response()->json(['status' => 'error', 'message' => 'Missing invoice data'], 400);
            }

            $invoice = Invoices::where('invoice_id', $invoiceData['invoice_id'])->first();

            if (!$invoice) {

                $invoice = new Invoices();

                $invoice->invoice_id = $invoiceData['invoice_id'];

                $invoice->invoice_number = $invoiceData['invoice_number'] ?? null;

                $invoice->payment_method = $payment_method;

                $invoice->created_at = now();


                Log::info("Created new invoice: " . $invoiceData['invoice_id']);
            } else {



                Log::info("Updated invoice: " . $invoiceData['invoice_id']);
            }

            $invoice->invoice_date = $invoiceData['invoice_date'] ?? null;

            $invoice->credits_applied = $invoiceData['credits_applied'] ?? null;

            $invoice->discount = $invoiceData['discount_total'] ?? null;

            $invoice->balance = $invoiceData['balance'] ?? null;



            $invoice->payment_made = $invoiceData['payment_made'] ?? null;

            $invoice->invoice_link = $invoiceData['invoice_url'] ?? null;
            $invoice->zoho_cust_id = $invoiceData['customer_id'] ?? null;

            $invoice->status = $invoiceData['status'] ?? null;

            $invoice->subscription_id = [
                "subscription_id" => $invoiceData['subscriptions'][0]['subscription_id'] ?? null,
            ];
            $invoice->invoice_items = [
                "code" => $invoiceData['invoice_items'][0]['code'],
                "quantity" => $invoiceData['invoice_items'][0]['quantity'],
                "item_id" => $invoiceData['invoice_items'][0]['item_id'],
                "discount_amount" => $invoiceData['invoice_items'][0]['discount_amount'],
                "tax_name" => $invoiceData['invoice_items'][0]['tax_name'],
                "description" => $invoiceData['invoice_items'][0]['description'],
                "item_total" => $invoiceData['invoice_items'][0]['item_total'],
                "item_custom_fields" => $invoiceData['invoice_items'][0]['item_custom_fields'],
                "tax_id" => $invoiceData['invoice_items'][0]['tax_id'],
                "tags" => $invoiceData['invoice_items'][0]['tags'],
                "unit" => $invoiceData['invoice_items'][0]['unit'],
                "account_id" => $invoiceData['invoice_items'][0]['account_id'],
                "tax_type" => $invoiceData['invoice_items'][0]['tax_type'],
                "price" => $invoiceData['invoice_items'][0]['price'],
                "product_id" => $invoiceData['invoice_items'][0]['product_id'],
                "account_name" => $invoiceData['invoice_items'][0]['account_name'],
                "name" => $invoiceData['invoice_items'][0]['name'],
                "tax_percentage" => $invoiceData['invoice_items'][0]['tax_percentage'],
            ];

            if ($invoiceData['payments']) {

                $invoice->payment_details =  [
                    'payment_id' => $invoiceData['payments'][0]['payment_id'],
                    'payment_mode' => $invoiceData['payments'][0]['payment_mode'],
                    'reference_number' => $invoiceData['payments'][0]['reference_number'],
                    'payment_date' => $invoiceData['payments'][0]['date'],
                    'payment_amount' => $invoiceData['payments'][0]['amount'],

                ];
            }


            $invoice->updated_at = now();

            try {

                $invoice->save();
            } catch (\Exception $e) {

                Log::error("Failed to save invoice: " . $e->getMessage());

                return response()->json(['status' => 'error', 'message' => 'Failed to save invoice'], 500);
            }

            return response()->json(['status' => 'success', 'message' => 'Invoice processed'], 200);
        } else {
            return response()->json(['status' => 'ignored', 'message' => 'Event type not supported'], 200);
        }
    }


    public function handleCreditNote(Request $request)
    {
        $data = $request->json()->all();

        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Invalid JSON data'], 400);
        }

        $eventType = $data['event_type'] ?? null;

        if ($eventType === 'creditnote_deleted') {
            return response()->json(['status' => 'ignored', 'message' => 'Invalid Operation'], 200);
        }

        $creditNoteData = $data['data']['creditnote'] ?? null;

        if (!$creditNoteData) {
            return response()->json(['status' => 'error', 'message' => 'Missing credit note data'], 400);
        }

        $creditNote = CreditNotes::where('creditnote_id', $creditNoteData['creditnote_id'])->first();

        if (!$creditNote) {
            $this->storeNewCreditNote($creditNoteData);
        } else {
            $this->updateExistingCreditNote($creditNote, $creditNoteData);
        }

        return response()->json(['status' => 'success', 'message' => 'Credit note processed'], 200);
    }

    private function storeNewCreditNote(array $creditNoteData)
    {
        $creditNote = new CreditNotes();

        $creditNote->creditnote_id = $creditNoteData['creditnote_id'];
        $creditNote->creditnote_number = $creditNoteData['creditnote_number'];
        $creditNote->credited_date = $creditNoteData['date'];
        $creditNote->invoice_number = $this->getInvoiceNumbersFromDatabase($creditNoteData['invoice_id']);
        $creditNote->credited_amount = $creditNoteData['total'];
        $creditNote->balance = $creditNoteData['balance'] ?? 0;
        $creditNote->status = $creditNoteData['status'];
        $creditNote->zoho_cust_id = $creditNoteData['customer_id'];
        $creditNote->created_at = now();
        $creditNote->updated_at = now();

        try {
            $creditNote->save();
            Log::info("Created new credit note: " . $creditNoteData['creditnote_id']);
        } catch (\Exception $e) {
            Log::error("Failed to create new credit note: " . $e->getMessage());
            throw new \Exception('Failed to save credit note');
        }
    }

    private function updateExistingCreditNote(CreditNotes $creditNote, array $creditNoteData)
    {
        $creditNote->credited_date = $creditNoteData['date'];
        $creditNote->invoice_number = $this->getInvoiceNumbersFromDatabase($creditNoteData['invoice_id']);
        $creditNote->credited_amount = $creditNoteData['total'];
        $creditNote->balance = $creditNoteData['balance'] ?? 0;
        $creditNote->status = $creditNoteData['status'];
        $creditNote->zoho_cust_id = $creditNoteData['customer_id'];
        $creditNote->updated_at = now();

        try {
            $creditNote->save();
            Log::info("Updated credit note: " . $creditNoteData['creditnote_id']);
        } catch (\Exception $e) {
            Log::error("Failed to update credit note: " . $e->getMessage());
            throw new \Exception('Failed to save credit note');
        }
    }

    private function getInvoiceNumbersFromDatabase($invoiceId)
    {
        if (empty($invoiceId)) {
            return '-';
        }

        if (!is_array($invoiceId)) {
            $invoiceId = [$invoiceId];
        }

        $invoiceNumbers = Invoices::whereIn('invoice_id', $invoiceId)->pluck('invoice_number')->toArray();

        return !empty($invoiceNumbers) ? implode(',', $invoiceNumbers) : '-';
    }

    public function handleRefund(Request $request)
    {
        $data = $request->json()->all();

        $eventType = $data['event_type'] ?? null;

        if ($eventType === 'payment_refunded') {
            $refundData = $data['data']['refund'];
            $refund = Refund::where('refund_id', $refundData['refund_id'])->first();
            if (!$refund) {
                $refund = new Refund();
                $existing_parent_refund = Refund::where('parent_payment_id', $refundData['autotransaction']['parent_payment_id'])->latest('created_at')->first();
                $invoice = Invoices::where('payment_details->payment_id',  $refundData['autotransaction']['parent_payment_id'])->first();
                if ($existing_parent_refund) {
                    $refund->balance_amount = $existing_parent_refund->balance_amount -  $refundData['creditnote']['refund_amount'];
                } else {
                    $refund->balance_amount = round($invoice->payment_made, 2) -  $refundData['creditnote']['refund_amount'];
                }


                Log::info("Created new refund: " . $refundData['refund_id']);

                $refund->created_at = now();
            } else {

                Log::info("Updated refund: " . $refundData['refund_id']);
            }


            $refund->refund_id = $refundData['refund_id'];
            $refund->creditnote_id = $refundData['creditnote']['creditnote_id'];
            $refund->creditnote_number = $refundData['creditnote']['creditnote_number'];
            $refund->refund_amount = $refundData['creditnote']['refund_amount'];
            $refund->zoho_cust_id = $refundData['customer_id'];
            $refund->date = $refundData['date'];
            $refund->description = $refundData['description'];
            $refund->parent_payment_id =  $refundData['autotransaction']['parent_payment_id'];
            $refund->status = $refundData['status'];
            $refund->refund_mode =  $refundData['refund_mode'];
            $refund->gateway_transaction_id =  $refundData['autotransaction']['gateway_transaction_id'];
            $refund->payment_method_id = $refundData['autotransaction']['card_id'];
            $refund->save();
            return response()->json(['status' => 'success', 'message' => 'Refund details processed'], 200);
        }
        return response()->json(['status' => 'ignored', 'message' => 'Event not handled'], 200);
    }



    public function handlePaymentMethod(Request $request)
    {
        $data = $request->json()->all();

        $eventType = $data['event_type'] ?? null;

        if ($eventType === 'payment_method_added') {

            $paymentMethodData = $data['data']['payment_method'];

            $card = PaymentMethod::where('card_id', $paymentMethodData['payment_method_id'])->first();

            if (!$card) {

                $card = new PaymentMethod();

                Log::info("Created new card: " . $paymentMethodData['payment_method_id']);

                $card->created_at = now();
            } else {

                Log::info("Updated card: " . $paymentMethodData['payment_method_id']);
            }

            $card->card_id = $paymentMethodData['payment_method_id'];

            $card->last_four_digits = $paymentMethodData['last_four_digits'];
            $card->payment_gateway = $paymentMethodData['payment_gateway'];

            $card->expiry_month = $paymentMethodData['expiry_month'];

            $card->expiry_year = $paymentMethodData['expiry_year'];

            $card->zoho_cust_id = $paymentMethodData['customer']['customer_id'];

            $card->updated_at = now();
            $card->save();

            return response()->json(['status' => 'success', 'message' => 'Card details processed'], 200);
        }

        return response()->json(['status' => 'ignored', 'message' => 'Event not handled'], 200);
    }

    public function handleInvoiceOld(Request $request)
    {
        $data = $request->json()->all();

        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Invalid JSON data'], 400);
        }

        $eventType = $data['event_type'] ?? null;

        if ($eventType !== 'invoice_notification') {

            return response()->json(['status' => 'ignored', 'message' => 'Not an invoice notification event'], 200);
        }

        $invoiceData = $data['data']['invoice'] ?? null;

        if (!$invoiceData) {
            return response()->json(['status' => 'error', 'message' => 'Missing invoice data'], 400);
        }

        $invoice = Invoices::where('invoice_id', $invoiceData['invoice_id'])->first();

        if (!$invoice) {

            $invoice = new Invoices();
            $invoice->invoice_id = $invoiceData['invoice_id'];
            $invoice->invoice_number = $invoiceData['invoice_number'] ?? null;
            Log::info("Created new invoice: " . $invoiceData['invoice_id']);
        } else {

            Log::info("Updated invoice: " . $invoiceData['invoice_id']);
        }

        $invoice->invoice_date = $invoiceData['invoice_date'] ?? null;
        $invoice->credits_applied = $invoiceData['credits_applied'] ?? null;
        $invoice->discount = $invoiceData['discount_total'] ?? null;
        $invoice->balance = $invoiceData['balance'] ?? null;
        $invoice->payment_made = $invoiceData['payment_made'] ?? null;
        $invoice->invoice_link = $invoiceData['invoice_url'] ?? null;
        $invoice->zoho_cust_id = $invoiceData['customer_id'] ?? null;
        $invoice->subscription_id = [
            "subscription_id" => $invoiceData['subscriptions'][0]['subscription_id'] ?? null,
        ];
        $invoice->invoice_items = [
            "code" => $invoiceData['invoice_items'][0]['code'],
            "quantity" => $invoiceData['invoice_items'][0]['quantity'],
            "item_id" => $invoiceData['invoice_items'][0]['item_id'],
            "discount_amount" => $invoiceData['invoice_items'][0]['discount_amount'],
            "tax_name" => $invoiceData['invoice_items'][0]['tax_name'],
            "description" => $invoiceData['invoice_items'][0]['description'],
            "item_total" => $invoiceData['invoice_items'][0]['item_total'],
            "item_custom_fields" => $invoiceData['invoice_items'][0]['item_custom_fields'],
            "tax_id" => $invoiceData['invoice_items'][0]['tax_id'],
            "tags" => $invoiceData['invoice_items'][0]['tags'],
            "unit" => $invoiceData['invoice_items'][0]['unit'],
            "account_id" => $invoiceData['invoice_items'][0]['account_id'],
            "tax_type" => $invoiceData['invoice_items'][0]['tax_type'],
            "price" => $invoiceData['invoice_items'][0]['price'],
            "product_id" => $invoiceData['invoice_items'][0]['product_id'],
            "account_name" => $invoiceData['invoice_items'][0]['account_name'],
            "name" => $invoiceData['invoice_items'][0]['name'],
            "tax_percentage" => $invoiceData['invoice_items'][0]['tax_percentage'],
        ];

        if ($invoiceData['payments']) {

            $invoice->payment_details =  [
                'payment_id' => $invoiceData['payments'][0]['payment_id'],
                'payment_mode' => $invoiceData['payments'][0]['payment_mode'],
                'reference_number' => $invoiceData['payments'][0]['reference_number'],
                'payment_date' => $invoiceData['payments'][0]['date'],
                'payment_amount' => $invoiceData['payments'][0]['amount'],

            ];
        }
        $invoice->created_at = now();
        $invoice->updated_at = now();

        try {
            $invoice->save();
        } catch (\Exception $e) {

            Log::error("Failed to save invoice: " . $e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Failed to save invoice'], 500);
        }

        return response()->json(['status' => 'success', 'message' => 'Invoice processed'], 200);
    }

    public function handleSubscriptionOld(Request $request)
    {
        $data = $request->json()->all();

        $eventType = $data['event_type'] ?? null;

        if (in_array($eventType, [
            'subscription_created',
            'subscription_renewed',
            'subscription_upgraded',
            'subscription_downgraded',
            'subscription_unpaid',
            'subscription_cancelled',
            'subscription_reactivated',
            'subscription_cancellation_scheduled',
            'subscription_scheduled_cancellation_removed',
            'subscription_activation'
        ])) {


            $customer = $data['data']['subscription']['customer'];

            $partner = Partner::where('zoho_cust_id', $customer['customer_id'])->first();

            if ($partner) {

                $subscriptionData = $data['data']['subscription'];

                $subscription = Subscriptions::where('subscription_id', $subscriptionData['subscription_id'])->first();

                if ($eventType === 'subscription_renewed') {

                    $subscription->addon =  null;

                    $subscription->save();
                }

                if (!$subscription) {

                    $subscription = new Subscriptions();

                    $subscription->subscription_number = $subscriptionData['subscription_number'];

                    $subscription->subscription_id = $subscriptionData['subscription_id'];

                    Log::info("Created new subscription: " . $subscriptionData['subscription_id']);
                } else {

                    Log::info("Updated subscription: " . $subscriptionData['subscription_id']);
                }


                $subscription->start_date = $subscriptionData['current_term_starts_at'];

                $subscription->next_billing_at = $subscriptionData['next_billing_at'];

                $subscription->plan_id = $subscriptionData['plan']['plan_id'];

                $subscription->zoho_cust_id = $subscriptionData['customer']['customer_id'];

                if (isset($subscriptionData['child_invoice_id'])) {

                    $subscription->invoice_id = $subscriptionData['child_invoice_id'];
                } else {
                    $subscription->invoice_id = "no invoice found";
                }

                if (isset($subscriptionData['card']['card_id'])) {

                    $subscription->payment_method_id = $subscriptionData['card']['card_id'];
                }

                if (isset($subscriptionData['bank_account']['account_id'])) {

                    $subscription->payment_method_id = $subscriptionData['bank_account']['account_id'];
                }

                $subscription_history = new SubscriptionHistory();
                $subscription_history->subscription_id = $subscriptionData['subscription_id'];
                $subscription_history->zoho_cust_id = $subscriptionData['customer']['customer_id'];
                $subscription_history->timestamp = now();
                $subscription_history->event_details = $data;
                $subscription_history->event_type = $data['event_type'];
                $subscription_history->save();


                $addon = isset($subscriptionData['addons'][0]['addon_code']) ? $subscriptionData['addons'][0]['addon_code'] : '';

                if (!empty($addon)) {

                    $subscription->addon = $addon;
                }
                if ($eventType == 'subscription_renewed') {

                    $subscription->addon = null;
                }

                $subscription->status = $subscriptionData['status'];

                $subscription->updated_at = now();

                $subscription->save();

                $this->storePartnerData($subscriptionData);

                $this->storePaymentData($subscriptionData);

                return response()->json(['status' => 'success', 'message' => 'Webhook processed'], 200);
            } else {
                return response()->json(['status' => 'success', 'message' => 'Customer not found'], 200);
            }
        } else {
            return response()->json(['status' => 'ignored', 'message' => 'Event not handled'], 200);
        }
    }
}
