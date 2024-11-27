@extends('layouts.partner_template')

@section('content')
@if($current_subscription && $metrics)
<div class="container">
    <h2 class="mt-2">{{ $partner->company_name }} Clicks Report</h2>
    <!-- Filter Form -->
    <form method="GET" action="{{ route('partner.reports', [], false) }}" class="mb-4 mt-4">
        <div class="row">
            <!-- Filter Dropdown -->
            <div class="col-md-2">
                <label for="filter" class="fw-bold">Filter:</label>
                <select name="filter" class="form-control" id="filter">
                    <option value="mtd" @selected(request('filter')==='mtd' )>Month to Date</option>
                    <option value="this_month" @selected(request('filter')==='this_month' )>This Month</option>
                    <option value="last_12_months" @selected(request('filter')==='last_12_months' )>Last 12 Months</option>
                    <option value="last_6_months" @selected(request('filter')==='last_6_months' )>Last 6 Months</option>
                    <option value="last_3_months" @selected(request('filter')==='last_3_months' )>Last 3 Months</option>
                    <option value="last_1_month" @selected(request('filter')==='last_1_month' )>Last 1 Month</option>
                    <option value="last_7_days" @selected(request('filter')==='last_7_days' )>Last 7 Days</option>
                </select>
            </div>

            <!-- Data Split Dropdown -->
            <div class="col-md-2">
                <div class="form-group">
                    <label class="fw-bold" for="data_split">Show data by</label>

                    @if($is_daily_plan)
                    <select name="data_split" id="data_split" class="form-control">
                        <option value="monthly" @selected(request('data_split')==='monthly' )>Month</option>
                        <option value="weekly" @selected(request('data_split')==='weekly' )>Week</option>
                        <option value="daily" @selected(request('data_split')==='daily' )>Day</option>
                    </select>
                    @elseif($is_weekly_plan)
                    <select name="data_split" id="data_split" class="form-control">
                        <option value="monthly" @selected(request('data_split')==='monthly' )>Month</option>
                        <option value="weekly" @selected(request('data_split')==='weekly' )>Week</option>
                    </select>
                    @elseif($is_monthly_plan)
                    <select name="data_split" id="data_split" class="form-control">
                        <option value="monthly" @selected(request('data_split')==='monthly' )>Month</option>
                    </select>
                    @endif

                </div>
            </div>


            <!-- Apply Button -->
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn button-clearlink text-primary fw-bold">Apply</button>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('partner.reports.export', ['id' => $partner->id,'filter' => request('filter', 'mtd'), 'data_split' =>  request('data_split', 'daily')], false) }}" class="btn btn-primary d-flex align-items-center">Download CSV</a>
            </div>
        </div>
    </form>

    <!-- Metrics Section -->
    <div class="row mb-5">
        <!-- Total Clicks Card -->
        <div class="col-md-2">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h2 class="card-title">{{ number_format($metrics->total_clicks) }}</h2>
                    <p class="card-text text-secondary">Total Clicks</p>
                </div>
            </div>
        </div>

        <!-- Conversion Rate Card -->
        <div class="col-md-2">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h2>{{ number_format($metrics->conversion_rate, 2) }}%</h2>
                    <p class="card-text text-secondary">Conversion Rate</p>
                </div>
            </div>
        </div>

        <!-- Total Cost Card -->
        <div class="col-md-2">
            <div class="card text-center shadow">
                <div class="card-body">
                    <p class="card-title fs-5 fw-bold"><strong>${{ number_format($metrics->total_cost, 2) }}</strong></p>
                    <p class="card-text text-secondary">Total Cost</p>
                </div>
            </div>
        </div>
        @if($budget_cap->clicks_pace_toggle)
        <div class="col-md-2" id="clicksPace">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h2 class="card-title">{{ number_format($metrics->clicks_pace, 2) }}</h2>
                    <p class="card-text text-secondary">Clicks Pace (MTD)</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Invoice Pace Card -->
        @if($budget_cap->invoice_pace_toggle)
        <div class="col-md-2" id="invoicePace">
            <div class="card text-center shadow">
                <div class="card-body">
                    @php
                    $budget_limit = $budget_cap->plan_type === 'flat' ? $budget_cap->click_limit : $budget_cap->cost_limit;
                    $budget_cap_hit = $metrics->invoice_pace >$budget_limit;
                    @endphp
                    <p class="{{$budget_cap_hit?'text-danger card-title fs-5 fw-bold':'$card-title fs-5 fw-bold'}}"><strong>${{ number_format($metrics->invoice_pace, 2) }}</strong></p>
                    <p class="{{$budget_cap_hit?'text-danger card-text text-secondary':' card-text text-secondary'}}">Invoice Pace (MTD)</p>
                </div>
            </div>
        </div>
        @endif
        @if($budget_cap->budget_cap_toggle)
        <div class="col-md-2">
            <div class="card text-center shadow">
                <div class="card-body">
                    @if($budget_cap->plan_type === 'flat')
                    <p class="card-title fs-5 fw-bold"><strong>${{ number_format($budget_cap->click_limit, 2) }}</strong></p>
                    @else
                    <p class=" card-title fs-5 fw-bold"><strong>${{ number_format($budget_cap->cost_limit, 2) }}</strong></p>
                    @endif
                    <p class="card-text text-secondary">Budget Cap </p>
                </div>
            </div>
        </div>
        @endif
    </div>




    <!-- Date Range Section -->
    <div class="text-center mb-4">
        <p>Data from: <strong>{{ $dateFrom->format('d M Y') }}</strong> to <strong>{{ $dateTo->format('d M Y') }}</strong></p>
    </div>

    <!-- Chart Section -->
    <div class=" mb-5">
        <canvas id="clicksChart"></canvas>
    </div>
</div>
@else
<div style="margin-top: 300px;" class="d-flex justify-content-center align-items-center ">
    <h3>No Usage Reports Found</h3>
</div>
@endif

@include('layouts.show-alert-modal')

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

<script>
    var chartData = @json($chartData);

    var labels = chartData.map(function(data) {
        return data.date;
    });
    var totalClicks = chartData.map(function(data) {
        return data.total_clicks;
    });


    var ctx = document.getElementById('clicksChart').getContext('2d');
    var clicksChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Clicks',
                data: totalClicks,
                borderColor: '#3498db',
                fill: false,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                }
            }
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var filter = document.getElementById('filter');
        var clicksPace = document.getElementById('clicksPace');
        var invoicePace = document.getElementById('invoicePace');


        function updateVisibility() {
            var selectedValue = filter.value;

            if (selectedValue === 'mtd') {
                clicksPace.style.display = 'block';
                invoicePace.style.display = 'block';
            } else {
                clicksPace.style.display = 'none';
                invoicePace.style.display = 'none';
            }
        }

        updateVisibility();

        filter.addEventListener('change', updateVisibility);
    });
</script>
@endsection