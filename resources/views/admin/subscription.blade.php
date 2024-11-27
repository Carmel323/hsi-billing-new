@extends('layouts.admin_template')

@section('content')

<div class="d-flex flex-column w-100">
    <div class="mb-2 mt-4 w-100">
        <div class="d-flex flex-row justify-content-between">
            <div>
                <h2 class="mt-2 mb-5">Subscriptions</h2>
            </div>
        </div>
    </div>



    <div class="top-row w-100 mb-4 mt-4">
        <div class="row">
            <div class="col-md-11">
                <form action="{{ route('admin.subscription',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
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
                            <label for="per_page">Show:</label>
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
            <form action="{{ route('admin.subscription',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
                <div class="col-md-1">
                    <div class="input-group mt-2">
                        <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($subscriptions->isNotEmpty())

    <div class="tables w-100">
        <table class="text-center mt-1 table table-bordered">
            <thead class="bg-clearlink fw-bold">
                <tr>
                    <th>S.No</th>
                    <th>Subscription Number</th>
                    <th>Company Name</th>
                    <th>Plan Name</th>
                    <th>Plan Amount(USD)</th>
                    <th>Add-On</th>
                    <th>Add-On Amount(USD)</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Invoices</th>
                </tr>
            </thead>

            <tbody id="invoiceTable">
                @foreach($subscriptions as $index => $subscription)
                <tr class="py-3 text-center ">

                    <td>{{ $subscriptions->total() - (($subscriptions->currentPage() - 1) * $subscriptions->perPage()) - $loop->index }}</td>

                    <td data-label="Subscription Number">{{$subscription->subscription_number}}

                        <div style="display: none;">
                            <p>subs_row_id: {{$subscription->id}}</p>
                            <p>subs_id : {{$subscription->subscription_id }}</p>
                            <p>cust_id : {{$subscription->zoho_cust_id }}</p>
                        </div>

                    </td>
                    
                    <td data-label="Company Name">
                        {{ isset($subscription->partner) ? $subscription->partner->company_name : '' }}
                    </td>

                    <td data-label="Plan Name">{{ isset($subscription->plan) ? $subscription->plan->plan_name : '' }}</td>
                    <td data-label="Amount">{{ isset($subscription->plan) ? $subscription->plan->price : '' }}</td>
                    @if($subscription->addon)
                    @php
                    $addon = DB::table('add_ons')->where('addon_code',$subscription->addon)->first();
                    @endphp
                    <td data-label="Addon Name">Yes</td>
                    <td data-label="Amount">{{$addon->addon_price}}</td>
                    @else
                    <td data-label="Addon Name">-</td>
                    <td data-label="Amount">-</td>
                    @endif
                    <td data-label="start_date">{{ Carbon\Carbon::parse($subscription->start_date)->format('d-M-Y') }}</td>
                    <td data-label="Next Billing">{{ Carbon\Carbon::parse($subscription->next_billing_at)->format('d-M-Y') }}</td>
                    <td data-label="Status" class="{{$subscription->status === 'live'? 'text-success':'text-danger'}}">{{$subscription->status}}</td>
                    <td><a href="/admin/invoice?&search={{$subscription->zoho_cust_id}}" id="show-customer" data-url="" class="btn button-clearlink text-primary fw-bold">View </a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-2 mb-5 paginate">
        <div class="row">
            <div class="col-md-12">
                <div class="pagination">
                    @if ($subscriptions->lastPage() > 1)
                    <ul class="pagination">
                        <li class="{{ ($subscriptions->currentPage() == 1) ? 'disabled' : '' }}">
                            <a href="{{ $subscriptions->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
                        </li>
                        @for ($i = 1; $i <= $subscriptions->lastPage(); $i++)
                            <li class="{{ ($subscriptions->currentPage() == $i) ? 'active' : '' }}">
                                <a href="{{ $subscriptions->appends(request()->query())->url($i) }}" class="page-link{{ ($subscriptions->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
                            </li>
                            @endfor
                            <li class="{{ ($subscriptions->currentPage() == $subscriptions->lastPage()) ? 'disabled' : '' }}">
                                <a href="{{ $subscriptions->appends(request()->query())->url($subscriptions->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
                            </li>
                    </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>
    @else
    <div class="d-flex justify-content-center align-items-center mt-5">
        <h3>No Subscriptions found.</h3>
    </div>
    @endif
</div>

@endsection