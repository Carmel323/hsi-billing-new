@extends('layouts.view-partner-template')

@section('child-content')

<div style="width:80%" class="d-flex flex-row justify-content-between mt-5">
    <div>
        <h5 class="fw-bold">Refunds</h5>
    </div>
</div>

<div style="width:80%" class="top-row mt-4">
    <div class="row">
        <div class="col-md-11">
            <form action="{{ route('view.partner.refunds',['id' => $partner->id],false) }}" method="GET" class="row g-3 align-items-center w-100">
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
            <form action="{{ route('view.partner.refunds',['id' => $partner->id],false) }}" method="GET" class="row g-3 align-items-center w-100">
                <div class="col-md-1">
                    <div class="input-group mt-2">
                        <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>
<div class="card border border-dark ms-0 m-2">
    <div class="card-body table-responsive p-0">
        @if($refunds->isNotEmpty())
        <table class="text-center mt-1 table border-dark m-0  rounded">
            <thead>
                <tr>
                    <th class="fw-normal">Creation Date</th>
                    <!-- <th class="fw-normal">Balance Amount</th> -->
                    <th class="fw-normal">Refund Amount</th>
                    <th class="fw-normal">Description</th>
                    <th class="fw-normal">Creditnote Number</th>
                    <th class="fw-normal">Gateway Transaction ID</th>
                    <th class="fw-normal">Invoice Number</th>
                    <th class="fw-normal">Refund Mode</th>
                    <th class="fw-normal">Status</th>
                    <!-- <th class="fw-normal">Invoice</th> -->
                </tr>
            </thead>

            <tbody>
                @foreach($refunds as $index => $refund)
                <tr class="py-3 text-center ">
                    <td data-label="start_date" class="fw-bold">{{ Carbon\Carbon::parse($refund->date)->format('d-M-Y') }}</td>
                    <!-- <td data-label="Balance Amount">{{$refund->balance_amount}}</td> -->
                    <td class="p-2 fw-bold" data-label="Refund Amount">{{ $refund->refund_amount }}</td>
                    <td data-label="Description">{{$refund->description}}</td>
                    <td data-label="Creditnote Number" class="text-primary">{{$refund->creditnote_number}}</td>
                    <td data-label="Gateway Transaction ID">{{$refund->gateway_transaction_id}}</td>
                    @php
                    $invoice = DB::table('invoices')->whereJsonContains('payment_details->payment_id',$refund->parent_payment_id)
                    ->first();
                    @endphp
                    <td data-label="Invoice Number" class="text-primary">{{$invoice->invoice_number}}</td>
                    <td data-label="Refund Mode" class="fw-bold">{{$refund->refund_mode}}</td>
                    <td data-label="Status" class="{{$refund->status === 'success'? 'text-success':'text-danger'}}">{{$refund->status}}</td>
                    <!-- <td><a href="/admin/view-partner/{{$partner->id}}/invoices?&search={{$refund->parent_payment_id}}" id="show-customer" data-url="" class="btn btn-sm btn-primary">View </a></td> -->
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
        <p class="m-0 p-0">No Refunds made</p>
    </div>
    @endif
</div>
</div>
<div class=" mt-2 mb-5 paginate">
    <div class="row">
        @if($refunds->isNotEmpty())
        <div class="col-lg mt-4">
            Total Count: <strong>{{$totalCount}}</strong>
        </div>
        @endif

        <div class="pagination col-lg mt-4">
            @if ($refunds->lastPage() > 1)
            <ul class="pagination">
                <li class="{{ ($refunds->currentPage() == 1) ? 'disabled' : '' }}">
                    <a href="{{ $refunds->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
                </li>
                @for ($i = 1; $i <= $refunds->lastPage(); $i++)
                    <li class="{{ ($refunds->currentPage() == $i) ? 'active' : '' }}">
                        <a href="{{ $refunds->appends(request()->query())->url($i) }}" class="page-link{{ ($refunds->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
                    </li>
                    @endfor
                    <li class="{{ ($refunds->currentPage() == $refunds->lastPage()) ? 'disabled' : '' }}">
                        <a href="{{ $refunds->appends(request()->query())->url($refunds->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
                    </li>
            </ul>
            @endif
        </div>
    </div>
</div>
@endsection