@extends('layouts.admin_template')

@section('content')

<div class="d-flex flex-column w-100">
    <div class="mb-2 mt-4">
        <h2 class="mt-2 mb-5">Partner Clicks Data</h2>
    </div>

    <div class="top-row w-100">
        <div class="row">
            <div class="col-md-12">
                <form action="{{ route('admin.all-partner-clicks', [], false) }}" method="GET" class="row g-3 align-items-center w-100">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="month">Month</label>
                            <select name="month" id="month" class="form-select">
                                <option value="">Select Month</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->format('M') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="year">Year</label>
                            <input type="number" name="year" id="year" class="form-control" value="{{ request('year') }}" min="2000" max="{{ date('Y') }}" />
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
                        <div class="col-md-2">
                            <label for="search">Search</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search here...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group mt-4">
                                <button class="btn button-clearlink text-primary fw-bold" type="submit">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($partnerClicks->isNotEmpty())
    <div class="tables w-100">
        <table class="text-center mt-4 table table-bordered">
            <thead class="bg-clearlink fw-bold">
                <tr>
                    <th class="p-2">#</th>
                    <th class="p-2">Zoho Customer ID</th>
                    <th class="p-2">Company Name</th>
                    <th class="p-2">Primary Contact Email</th>
                    <th class="p-2">Subscribed Plan Name</th>
                    <th class="p-2">Subscribed Addon Name</th>
                    <th class="p-2">Subscription ID</th>
                    <th class="p-2">Plan Max Clicks</th>
                    <th class="p-2">Addon Max Clicks</th>
                    <th class="p-2">Total Max Clicks</th>
                    <th class="p-2">Month</th>
                    <th class="p-2">Year</th>
                    <th class="p-2">Unique Clicks Count</th>
                    <th class="p-2">Clicks Usage Percentage</th>
                </tr>
            </thead>

            <tbody>
                @foreach($partnerClicks as $click)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $click->partner_zoho_cust_id }}</td>
                    <td>{{ $click->partner_company_name }}</td>
                    <td>{{ $click->primary_contact_email }}</td>
                    <td>{{ $click->subscribed_plan_name }}</td>
                    <td>{{ $click->subscribed_addon_name }}</td>
                    <td>{{ $click->subscription_id }}</td>
                    <td>{{ $click->plan_max_clicks }}</td>
                    <td>{{ $click->addon_max_clicks }}</td>
                    <td>{{ $click->total_max_clicks }}</td>
                    <td>{{ \Carbon\Carbon::createFromFormat('m', $click->click_month)->format('M') }}</td>
                    <td>{{ $click->click_year }}</td>
                    <td>{{ $click->unique_clicks_count }}</td>
                    <td>{{ $click->clicks_usage_percentage }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-2 mb-5 paginate">
        <div class="row">
            <div class="col-md-12">
                <div class="pagination">
                    @if ($partnerClicks->lastPage() > 1)
                    <ul class="pagination">
                        <li class="{{ ($partnerClicks->currentPage() == 1) ? 'disabled' : '' }}">
                            <a href="{{ $partnerClicks->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
                        </li>
                        @for ($i = 1; $i <= $partnerClicks->lastPage(); $i++)
                        <li class="{{ ($partnerClicks->currentPage() == $i) ? 'active' : '' }}">
                            <a href="{{ $partnerClicks->appends(request()->query())->url($i) }}" class="page-link{{ ($partnerClicks->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
                        </li>
                        @endfor
                        <li class="{{ ($partnerClicks->currentPage() == $partnerClicks->lastPage()) ? 'disabled' : '' }}">
                            <a href="{{ $partnerClicks->appends(request()->query())->url($partnerClicks->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
                        </li>
                    </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @else
    <div class="d-flex justify-content-center align-items-center mt-5">
        <h3>No Clicks data found.</h3>
    </div>
    @endif
</div>

@endsection
