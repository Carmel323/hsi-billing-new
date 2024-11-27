@extends('layouts.partner_template')

@section('content')

<div class="thankyou-card shadow border-0 border-top border-primary m-3 mt-5 p-3 bg-clearlink">
    <div class="card-body ">
        <h4 class="fw-bold"> Subscription Status : <span class="status">Success</span></h4>
        <p>Please find the details below:</p>
        <div class="thankyou-table border m-2 rounded">
            <table style="width:100%" class="table m-0 ">
                <tr>
                    <td class="w-50">
                        <p class=" m-0  p-1 fw-bold ">Plan Name:</p>
                    </td>
                    <td class="w-50">
                        <p class=" m-0 p-1">{{ $planName}}</p>
                    </td>
                </tr>
                <tr>
                    <td class="w-50">
                        <p class="m-0  p-1 fw-bold  ">Subscription Number:</p>
                    </td>
                    <td class="w-50">
                        <p class=" m-0  p-1">{{ $subscription->subscription_number}}</p>
                    </td>
                </tr>
                <tr>
                    <td class="w-50">
                        <p class="m-0  p-1 fw-bold ">Subscription Amount(US$):</p>
                    </td>
                    <td class="w-50">
                        <p class=" m-0 p-1">${{number_format($planPrice,2)}}</p>
                    </td>
                </tr>
                <tr>
                    <td class="w-50">
                        <p class=" m-0  p-1  fw-bold  ">Next Billing Date:</p>
                    </td>
                    <td class="w-50">
                        <p class="m-0  p-1">{{ Carbon\Carbon::parse($subscription->next_billing_at)->format('d-M-Y') }}</p>
                    </td>
                </tr>
                <tr>
                    <td class="w-50">
                        <p class=" m-0 p-1 fw-bold">Invoice Number:</p>
                    </td>
                    <td class="w-50">
                        <p class="m-0  p-1">{{ $invoice->invoice_number}}</p>
                    </td>
                </tr>
                <tr>
                    <td class="w-50">
                        <p class="fw-bold m-0 p-1 ">Invoice Status:</p>
                    </td>
                    <td class="w-50">
                        <p class=" m-0  p-1">{{$invoice_status}}</p>
                    </td>
                </tr>
                <tr>
                    <td class="w-50">
                        <p class="  fw-bold  p-1">View Invoice:</p>
                    </td>
                    <td class="w-50">
                        <p class="m-0   p-1"><a href="{{$invoice_link}}" target="_blank" class="btn m-0 btn-sm btn-primary text-white">View Invoice</a></p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection