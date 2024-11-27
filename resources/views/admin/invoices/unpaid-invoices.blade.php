@extends('layouts.view-invoice-template')

@section('child-content')
<div class="d-flex flex-column w-100">
    <div class="mb-4 mt-5">
        <h5 class="fw-bold">UnPaid Invoices</h5>
    </div>

    <div class="top-row w-100">
        <div class="row">
            <div class="col-md-11">
                <form action="{{ route('admin.unpaid-invoice',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
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
        <div class="col-md-1">
            <form action="{{ route('admin.unpaid-invoice',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
                <div class="col-md-1">
                    <div class="input-group mt-2">
                        <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($invoices->isNotEmpty())

    <div class="tables w-100">
        <table class="text-center mt-4 table table-bordered">
            <thead class="bg-clearlink fw-bold">
                <tr>
                    <th class="p-2">#</th>
                    <th class="p-2">Invoice Date</th>
                    <th class="p-2">Invoice Number</th>
                    <th class="p-2">Payment Mode</th>
                    <th class="p-2">Company Name</th>
                    <th class="p-2">Plan Name</th>
                    <th class="p-2">Plan Price(USD)</th>
                    <th class="p-2">Invoice Price(USD)</th>
                    <th class="p-2">Credits Applied(USD)</th>
                    <th class="p-2">Payment Received(USD)</th>
                    <th class="p-2">Status</th>
                    <th class="p-2">Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoices->total() - (($invoices->currentPage() - 1) * $invoices->perPage()) - $loop->index }}</td>
                    <td class="p-2" data-label="Invoice Date">
                        {{ Carbon\Carbon::parse($invoice->invoice_date)->format('d-M-Y') }}
                    </td>
                    <td> <a href="{{ $invoice->invoice_link }}" target="_blank" class=" text-primary ">{{$invoice->invoice_number}}</a></td>
                    <!-- <td class="p-2" data-label="Payment Method"> @if($invoice->payment_method === 'card')
                        Card
                        @else
                        ACH
                        @endif</td> -->
                    <td class="p-2" data-label="Payment Method">{{ $invoice->payment_method }}</td>
                    <td class="p-2" data-label="Company Name">{{ isset($invoice->partner) ? $invoice->partner->company_name : '' }}</td>
                    <td class="p-2" data-label="Plan Name">{{$invoice->invoice_items['name']}}</td>
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
                    <td class="p-2" data-label="Price(USD)">{{number_format($invoice->invoice_items['price'],2)}}</td>
                    <td class="p-2" data-label="Credits Applied">{{$invoice->credits_applied}}</td>
                    <td class="p-2" data-label="Payment made(USD)">{{$invoice->payment_made}}</td>
                    <td class="p-2" data-label="Status">{{$invoice->status}}</td>
                    @if($invoice->status === "paid")
                    <td>-</td>
                    @else
                    <td class="p-2" data-label="Action"><a data-bs-toggle="modal" data-bs-target="#staticBackdrop2{{$invoice->invoice_id}}" class="btn btn-warning btn-sm">Record Payment</a></td>
                    @endif
                    <!-- <td class="p-2" data-label="Invoice Link">
                        <a href="{{ $invoice->invoice_link }}" target="_blank" class="btn button-clearlink text-primary fw-bold">Invoice Link</a>
                    </td> -->
                </tr>
                <div class="modal fade" id="staticBackdrop2{{$invoice->invoice_id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content text-white" style="background-color: #162b64;">
                            <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel"><strong> Payment for {{$invoice->invoice_number}}</strong></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="/record-payment" method="post">
                                    @csrf
                                    <h5>Plan Details</h5>
                                    <div class="mb-3 row">
                                        <div class="col-lg">
                                            <label for="name" class="form-label">Amount Received*</label>
                                            <input type="text" name="amount" class="form-control" value="{{round($invoice->invoice_items['price'] - $invoice->credits_applied,2)}}" readonly required>
                                        </div>
                                        <div class="col-lg">
                                            <label for="plan_code" class="form-label">Payment Date*</label>
                                            <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required />
                                        </div>
                                    </div>
                                    <div class=" mb-3 row">
                                        <div class="col-lg">
                                            <label for="payment_mode" class="form-label">Payment Mode</label>
                                            <select type="text" name="payment_mode" class="form-select" required>
                                                <option value="">Select</option>
                                                <option value="bank_account">ACH</option>
                                                <option value="card">Card</option>
                                            </select>
                                        </div>
                                        <div class="col-lg">
                                            <label for="reference_number" class="form-label">Reference Number#</label>
                                            <input type="text" name="reference_number" class="form-control" />
                                        </div>
                                        <input type="text" name="invoice_id" class="form-control" value="{{$invoice->invoice_id}}" hidden />
                                    </div>
                                    <div class=" mb-3 row">
                                        <div class="col-lg-6">
                                            <label for="bank_charges" class="form-label">Bank Charges</label>
                                            <input type="text" name="bank_charges" class="form-control" />
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-danger text-white px-3 py-2 rounded">Save<i class="fa-solid fa-chevron-right"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

            </tbody>
        </table>
    </div>
    <div class="mt-2 mb-5 paginate">
        <div class="row">
            <div class="col-md-12">
                <div class="pagination">
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

    </div>
    @else
    <div class="d-flex justify-content-center align-items-center mt-5">
        <h3>No Unpaid Invoices found.</h3>
    </div>
    @endif
</div>
@endsection