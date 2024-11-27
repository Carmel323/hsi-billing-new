@extends('layouts.view-partner-template')

@section('child-content')

<div style="width:80%" class="d-flex flex-row justify-content-between mt-5">
    <div>
        <h5 class="fw-bold">Invoices</h5>
    </div>
    <div class="d-flex flex-row">
        @if($subscription)
        <div>
            <button data-bs-toggle="modal" data-bs-target="#addCustomInvoiceModal" class="btn-sm btn btn-primary mt-2 me-2">Add Custom Invoice</button>
        </div>
        <div>
            <button data-bs-toggle="modal" data-bs-target="#refundPaymentModal" class="btn-sm btn btn-primary mt-2">Refund a payment</button>
        </div>
        @endif
    </div>
</div>

<div style="width:80%" class="top-row mt-4">
    <div class="row">
        <div class="col-md-11">
            <form action="{{ route('view.partner.invoices',['id' => $partner->id],false) }}" method="GET" class="row g-3 align-items-center w-100">
                <div class="row">
                    <div class="col-md-3">
                        <label class="">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="">End Date</label>
                        <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}" />
                    </div>
                    <div class="col-md-2">
                        <label for="per_page">Show</label>
                        <select name="per_page" id="per_page" class="form-select">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search here...">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="input-group mt-4">
                            <button class="btn button-clearlink text-primary fw-bold" type="submit">Submit</button>
                        </div>
                    </div>

                </div>
            </form>
        </div>

    </div>
    <div class="d-flex justify-content-between mt-1">
        <div>
            <form action="{{ route('view.partner.invoices',['id' => $partner->id],false) }}" method="GET" class="row g-3 align-items-center w-100">
                <div class="col-md-1">
                    <div class="input-group mt-2">
                        <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>
<div class="card border partner-card border-dark ms-0 m-2">
    <div class="card-body table-responsive p-0">
        @if($invoices->isNotEmpty())
        <table class="text-center mt-1 table border-dark m-0  rounded">
            <thead>
                <tr>
                    <th class="fw-normal">Creation Date</th>
                    <th class="fw-normal">Invoice Number</th>
                    <th class="fw-normal">Payment Mode</th>
                    <th class="fw-normal">Plan Name</th>
                    <th class="fw-normal">Plan Price(USD)</th>
                    <th class="fw-normal">Invoice Price(USD)</th>
                    <th class="fw-normal">Credits Applied(USD)</th>
                    <th class="fw-normal">Payment Received(USD)</th>
                    <th class="fw-normal">Status</th>

                </tr>
            </thead>

            <tbody id="invoiceTable">
                @foreach($invoices as $index => $invoice)
                <tr class="py-3 text-center ">
                    <td data-label="start_date" class="fw-bold">{{ Carbon\Carbon::parse($invoice->invoice_date)->format('d-M-Y') }}</td>
                    <td data-label="Invoice Number" class="text-primary"><a href="{{ $invoice->invoice_link }}" target="_blank" class=" text-primary ">{{$invoice->invoice_number}}</a></td>
                    <!-- <td class="p-2 fw-bold" data-label="Payment Method">
                        @if($invoice->payment_method === 'card')
                        Card
                        @else
                        ACH
                        @endif
                    </td> -->
                    <td class="p-2" data-label="Payment Method">{{ $invoice->payment_method }}</td>
                    <td data-label="Plan Name" class="fw-bold">{{$invoice->invoice_items['name']}}</td>
                    @php
                    $plan = DB::table('plans')->where('plan_name',$invoice->invoice_items['name'])->first();
                    if(!$plan){
                    $addon = DB::table('add_ons')->where('name',$invoice->invoice_items['name'])->first();
                    }
                    @endphp
                    @if($plan)
                    <td class="p-2" data-label="Plan Price(USD)">{{$plan->price}}</td>
                    @elseif($addon)
                    <td class="p-2" data-label="Plan Price(USD)">{{$addon->addon_price}}</td>
                    @else
                    <td class="p-2" data-label="Plan Price(USD)">-</td>
                    @endif
                    <td data-label="Amount" class="fw-bold">{{round($invoice->invoice_items['price'],2)}}</td>
                    <td class="p-2" data-label="Credits Applied">{{$invoice->credits_applied}}</td>
                    <td class="p-2 fw-bold" data-label="Payment made(USD)">{{$invoice->payment_made}}</td>
                    <td data-label="Status" class="{{$invoice->status === 'paid'? 'text-success':'text-danger'}}">{{$invoice->status}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>


    </div>
    <!-- <div class="d-flex justify-content-between m-0 p-0 align-items-center">
            <div class="d-flex align-items-center ">
                <p class="fw-bold m-0 p-4">Total Count</p>
            </div>
            <div>

            </div>
        </div> -->
    @else
    <div class="d-flex justify-content-center align-items-center m-5  ">
        <p class="m-0 p-0">No Invoices added</p>
    </div>
    @endif
</div>
<div class="modal fade" id="addCustomInvoiceModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content bg-popup">
            <div class=" modal-header">
                <div class="d-flex flex-column">
                    <h3 class="modal-title" id="exampleModalLabel">Add Custom Invoice</h3>

                </div>

                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body">
                <p class="body-text-small text-danger">Note: If you add Custom Invoice the respective amount will be charged from your partner automatically</p>
                <form action="/add-custom-invoice" method="post">
                    @csrf
                    <div class=" mb-3 ">
                        <label class="fw-bold">Amount*</label>
                        <input name="amount" class="ms-2 form-control" placeholder="Amount*" required>
                    </div>
                    <div class=" mb-3 ">
                        <label class="fw-bold">Description*</label>
                        <textarea class="form-control" name="description" placeholder="Description *" required></textarea>
                    </div>
                    <input name="subscription_id" class="ms-2 form-control" value="{{ isset($subscription->subscription_id) ? $subscription->subscription_id : ''}}" hidden>
                    <div class="modal-footer border-0 p-0 mt-3">
                        <input type="submit" class="btn btn-primary py-1 rounded " value="Save">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="refundPaymentModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content bg-popup">
            <div class=" modal-header">
                <div class="d-flex flex-column">
                    <h3 class="modal-title" id="exampleModalLabel">Refund a payment</h3>

                </div>

                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body">
                <form action="/refund-a-payment" method="post">
                    @csrf
                    <div class=" mb-3 ">
                        <label class="fw-bold">Invoice-Number*</label>
                        <select name="payment_id" class="form-control" required>
                            <option value="">Select an Invoice</option>
                            @if($invoices)
                            @foreach($invoices as $invoice)
                            @php

                            $paymentId = isset($invoice->payment_details['payment_id']) ? $invoice->payment_details['payment_id'] : null ;

                            $refund = DB::table('refunds')->where('parent_payment_id', $paymentId)->latest('created_at')->first();


                            $balance = $refund ?$refund->balance_amount : round($invoice->payment_made,2);

                            @endphp
                            @if($paymentId)
                            <option value="{{ $paymentId }}">{{ $invoice->invoice_number }} - ${{ round($invoice->invoice_items['price'],2)}}- balance({{$balance}})</option>
                            @endif
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <div class=" mb-3 ">
                        <label class="fw-bold">Amount*</label>
                        <span class="body-text-small text-dark">(Enter an amount less than the balance available)</span>

                        <input type="number" step="0.01" class="form-control" name="amount" placeholder="Enter the amount *" required />
                    </div>
                    <div class=" mb-3 ">
                        <label class="fw-bold">Description*</label>
                        <textarea class="form-control" name="description" placeholder="Description *" required></textarea>
                    </div>
                    <div class="modal-footer border-0 p-0 mt-3">
                        <input type="submit" class="btn btn-primary py-1 rounded " value="Save">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
<div class=" mt-2 mb-5 paginate">
    <div class="row">
        @if($invoices->isNotEmpty())
        <div class="col-lg mt-4">
            Total Count: <strong>{{$totalCount}}</strong>
        </div>
        @endif

        <div class="pagination col-lg mt-4">
            @if ($invoices->lastPage() > 1)
            <ul class="pagination">
                <li class="{{ ($invoices->currentPage() == 1) ? 'disabled' : '' }}">
                    <a href="{{ $invoices->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
                </li>
                @for ($i = 1; $i <= $invoices->lastPage(); $i++)
                    <li class="{{ ($invoices->currentPage() == $i) ? 'active' : '' }}">
                        <a href="{{ $invoices->appends(request()->query())->url($i) }}" class="page-link{{ ($invoices->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
                    </li>
                    @endfor
                    <li class="{{ ($invoices->currentPage() == $invoices->lastPage()) ? 'disabled' : '' }}">
                        <a href="{{ $invoices->appends(request()->query())->url($invoices->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
                    </li>
            </ul>
            @endif
        </div>
    </div>
</div>
@endsection