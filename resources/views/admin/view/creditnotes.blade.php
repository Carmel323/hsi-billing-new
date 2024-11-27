@extends('layouts.view-partner-template')

@section('child-content')

<div style="width:80%" class="d-flex flex-row justify-content-between mt-5">
    <div>
        <h5 class="fw-bold">Credit Notes</h5>
    </div>
    <div>
        <a data-bs-toggle="modal" data-bs-target="#createCreditnoteModal" style="cursor:pointer;" class="text-dark fw-bold"><i class="fa-solid fa-circle-plus me-2 text-primary"></i>Create a credit note</a>
    </div>
</div>

<div style="width:80%" class="top-row mt-4">
    <div class="row">
        <div class="col-md-11">
            <form action="{{ route('view.partner.creditnotes', ['id' => $partner->id], false) }}" method="GET" class="row g-3 align-items-center w-100">
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
        <form action="{{ route('view.partner.creditnotes', ['id' => $partner->id], false) }}" method="GET" class="row g-3 align-items-center w-100">
            <div class="col-md-1">
                <div class="input-group mt-2">
                    <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border partner-card border-dark ms-0 m-2">
    <div class="card-body table-responsive p-0">
        @if($creditnotes->isNotEmpty())
        <table class="text-center mt-1 table border-dark m-0  rounded">
            <thead>
                <tr>
                    <th class="fw-normal">Date</th>
                    <th class="fw-normal">Credit Note</th>
                    <th class="fw-normal">Company Name</th>
                    <th class="fw-normal">Invoice</th>
                    <th class="fw-normal">Status</th>
                    <th class="fw-normal">Amount</th>
                    <th class="fw-normal">Balance</th>
                    <th class="fw-normal">View</th>
                </tr>
            </thead>

            <tbody id="invoiceTable">
                @foreach($creditnotes as $index => $creditnote)
                <tr class="py-3 text-center ">
                    <td data-label="start_date" class="fw-bold">{{ Carbon\Carbon::parse($creditnote->credited_date)->format('d-M-Y') }}</td>
                    <td data-label="Subscription Number" class="text-primary">{{$creditnote->creditnote_number}}</td>
                    <td data-label="Customer Name" class="fw-bold">{{$creditnote->partner->company_name}}</td>
                    <td data-label="Amount">{{$creditnote->invoice_number}}</td>
                    <td data-label="Status" class="{{$creditnote->status === 'open'? 'text-success':'text-danger'}}">{{$creditnote->status}}</td>
                    <td data-label="Amount" class="fw-bold">{{$creditnote->credited_amount}}</td>
                    <td data-label="Amount" class="fw-bold">{{$creditnote->balance}}</td>
                    <td class="p-2 "><a href="/admin-view-creditnote/{{ $creditnote->creditnote_id }}" class="btn btn-sm btn-primary">Download PDF</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <!-- <div class="d-flex justify-content-between m-0 p-0 align-items-center">
            <div class="d-flex align-items-center ">
                <p class="fw-bold m-0 p-4">Total Count</p>
            </div>
            <div>

            </div>
        </div> -->
        @else
        <div class="d-flex justify-content-center align-items-center m-5  ">
            <p class="m-0 p-0">No CreditNotes added</p>
        </div>
        @endif
    </div>
</div>
<div class="mt-2 mb-5 paginate">
    <div class="row">
        @if($creditnotes->isNotEmpty())
        <div class="col-lg mt-4">
            Total Count: <strong>{{$totalCount}}</strong>
        </div>
        @endif
        <div class="pagination col-lg mt-4">
            @if ($creditnotes->lastPage() > 1)
            <ul class="pagination">
                <li class="{{ ($creditnotes->currentPage() == 1) ? 'disabled' : '' }}">
                    <a href="{{ $creditnotes->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
                </li>
                @for ($i = 1; $i <= $creditnotes->lastPage(); $i++)
                    <li class="{{ ($creditnotes->currentPage() == $i) ? 'active' : '' }}">
                        <a href="{{ $creditnotes->appends(request()->query())->url($i) }}" class="page-link{{ ($creditnotes->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
                    </li>
                    @endfor
                    <li class="{{ ($creditnotes->currentPage() == $creditnotes->lastPage()) ? 'disabled' : '' }}">
                        <a href="{{ $creditnotes->appends(request()->query())->url($creditnotes->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
                    </li>
            </ul>
            @endif
        </div>

    </div>
</div>
<div class="modal fade" id="createCreditnoteModal" tabindex="-1" aria-labelledby="updateSubscriptionModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-popup">
            @if($plan)
            <div class="modal-header">
                <h3 class="modal-title">Create a credit note</h3>
                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body">
                <form action="/add-creditnote" method="post">
                    @csrf
                    <label for="plan_code" class="fw-bold form-label mt-3">Current Subscription Plan Code *</label>
                    <input type="text" name="plan_code" class="form-control" value="{{ $plan->plan_code}}" readonly />
                    <label for="amount" class="fw-bold form-label mt-3">Amount*</label>
                    <input type="number" name="amount" class="form-control" max="{{ $plan->price}}" required>
                    <label for="description" class="fw-bold form-label mt-3">Description*</label>
                    <input type="text" name="description" class="form-control" required>
                    <input type="text" name="partner_id" value="{{$partner->id}}" hidden>
                    <button type="submit" class="btn btn-primary mt-3 mb-2">Save</button>
                </form>
                @else
                <div class="d-flex justify-content-center align-items-center p-5 position-relative">
                    <p class="lh-lg fs-4 fw-bold ">Please create a subscription to create a credit note</p>
                    <button type="button" class="close border-0 position-absolute top-0 end-0 mt-3" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection