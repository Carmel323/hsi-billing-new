<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNotes extends Model
{
    use HasFactory;

    public function retrieveCreditNote($creditnote_id, $invoiceNumber)
    {
        $creditnote_url = env('CREDITNOTES_URL');
        $token1 = AccessToken::latest('created_at')->first();
        $access_token = $token1->access_token;
        $client = new \GuzzleHttp\Client();

        $options = [
            'headers' => [
                'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
            ]
        ];

        $res = $client->request(
            'GET',
            $creditnote_url . $creditnote_id,
            $options
        );

        $response = (string) $res->getBody();
        $response = json_decode($response);
        $data = $response->creditnote;



        $creditNote = CreditNotes::where('creditnote_id', $creditnote_id)->first();

        if ($creditNote) {

            $invoices = $data->invoices;
            $credit_invoice_number = $invoices[0]->invoice_number;

            for ($i = 1; $i < count($invoices); $i++) {

                $credit_invoice_number = $credit_invoice_number . ',' . $invoices[$i]->invoice_number;
            }
            $creditNote->invoice_number = $credit_invoice_number;
            $creditNote->credited_date = $data->date;
            $creditNote->credited_amount = $data->total;
            $creditNote->balance = $data->balance;
            $creditNote->status = $data->status;
            $creditNote->save();
        } else {

            $creditNote = new CreditNotes();
            $creditNote->creditnote_id = $data->creditnote_id;
            $creditNote->creditnote_number = $data->creditnote_number;
            $creditNote->credited_date = $data->date;
            if ($data->invoices) {
                $creditNote->invoice_number = $data->invoices[0]->invoice_number;
            } else {
                $creditNote->invoice_number = $invoiceNumber;
            }
            $creditNote->credited_amount = $data->total;
            $creditNote->balance = $data->balance;
            $creditNote->status = $data->status;
            $creditNote->zoho_cust_id = $data->customer_id;
            $creditNote->save();
        }
    }
}
