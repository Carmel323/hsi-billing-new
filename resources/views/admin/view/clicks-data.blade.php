@extends('layouts.view-partner-template')

@section('child-content')
@if($metrics)
<div class="container">
    <!-- Filter Form -->
    <form method="GET" action="{{ route('view.partner.clicksdata', $partner->id) }}" class="mb-1 mt-4">
        <div class="row">
            <!-- Filter Dropdown -->
            <div class="col-md-2 mb-3">
                <label for="filter" class="fw-bold">Filter:</label>
                <select name="filter" class="form-control" id="filter">
                    <option value="mtd" @selected(request('filter')==='mtd' )>Month to Date</option>
                    <option value="this_month" @selected(request('filter')==='this_month' )>This Month</option>
                    <option value="last_12_months" @selected(request('filter')==='last_12_months' )>Last 12 Months</option>
                    <option value="last_6_months" @selected(request('filter')==='last_6_months' )>Last 6 Months</option>
                    <option value="last_3_months" @selected(request('filter')==='last_3_months' )>Last 3 Months</option>
                    <option value="last_1_month" @selected(request('filter')==='last_1_month' )>Last 1 Month</option>
                    <option value="last_7_days" @selected(request('filter')==='last_7_days' )>Last 7 Days</option>
                    <option value="custom" @selected(request('filter')==='custom' )>Custom Range</option>
                </select>
            </div>

            <div class="col-md-2 mb-3" id="dateDiv1" style="display: none;">
                <div class="form-group">
                    <label for="date_from" class="fw-bold">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{$dateFrom->format('Y-m-d')}}">
                </div>
            </div>
            <div class="col-md-2 mb-3" id="dateDiv2" style="display: none;">
                <div class="form-group">
                    <label for="date_to" class="fw-bold">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo->format('Y-m-d') }}">
                </div>
            </div>

            <!-- Data Split Dropdown -->
            <div class="col-md-2 mb-3">
                <label for="data_split" class="fw-bold">Data Split:</label>
                <select name="data_split" class="form-control" id="data_split">
                    <option value="daily" @selected(request('data_split')==='daily' )>Daily</option>
                    <option value="weekly" @selected(request('data_split')==='weekly' )>Weekly</option>
                    <option value="monthly" @selected(request('data_split')==='monthly' )>Monthly</option>
                </select>
            </div>


            <!-- Apply Button -->
            <div class="col-md-1 d-flex align-items-end mb-3">
                <button type="submit" class="btn button-clearlink text-primary fw-bold">Apply</button>
            </div>

            <div class="col-md-1 d-flex align-items-end mb-3">
                <a href="{{ route('view.partner.reports.export', ['id' => $partner->id,'filter' => request('filter', 'mtd'), 'data_split' =>  request('data_split', 'daily')], false) }}" class="btn btn-primary d-flex align-items-center">Report <i class="ms-2 fa-solid fa-download"></i></a>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class=" d-flex flex-row align-items-center ">
                <label for="clicks_pace" class="fw-bold">Show Clicks Pace to partner</label>
                <span id="toggle" class="text-primary fs-3 ms-3 cursor-pointer" data-toggle="clicks_pace_toggle" data-partner-id="{{$partner->id}}">
                    <i class="{{$budget_cap->clicks_pace_toggle?'fa-solid fa-toggle-on':'fa-solid fa-toggle-off'}}" id="toggle-icon"></i>
                </span>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class=" d-flex flex-row align-items-center">
                <label for="invoice_pace" class="fw-bold">Show Invoice Pace to partner</label>
                <span class="text-primary fs-3 ms-3 cursor-pointer" data-toggle="invoice_pace_toggle" data-partner-id="{{$partner->id}}">
                    <i class="{{$budget_cap->invoice_pace_toggle?'fa-solid fa-toggle-on':'fa-solid fa-toggle-off'}}"></i>
                </span>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class=" d-flex flex-row align-items-center">
                <label for="budget_cap" class="fw-bold">Show Budget Cap to partner</label>
                <span class="text-primary fs-3 ms-3 cursor-pointer" data-toggle="budget_cap_toggle" data-partner-id="{{$partner->id}}">
                    <i class="{{$budget_cap->budget_cap_toggle?'fa-solid fa-toggle-on':'fa-solid fa-toggle-off'}}"></i>
                </span>
            </div>
        </div>
    </div>

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
        <div class="col-md-2" id="clicksPace">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h2 class="card-title">{{ number_format($metrics->clicks_pace, 2) }}</h2>
                    <p class="card-text text-secondary">Clicks Pace (MTD)</p>
                </div>
            </div>
        </div>

        <!-- Invoice Pace Card -->
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

    </div>



    <!-- Date Range Section -->
    <div class="text-center mb-4">
        <p>Data from: <strong>{{ $dateFrom->format('d M Y') }}</strong> to <strong>{{ $dateTo->format('d M Y') }}</strong></p>
    </div>

    <!-- Chart Section -->
    <div class="chart mb-5">
        <h2 class="fw-bold text-center">Clicks Chart</h2>
        <canvas id="clicksChart"></canvas>
    </div>
</div>
@else
<div style="margin-top: 300px;" class="d-flex justify-content-center align-items-center ">
    <h3>No Clicks Data Found</h3>
</div>
@endif



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
    var domainWiseClicks = chartData.map(function(data) {
        return data.domain_clicks;
    });

    var domainColors = {
        "https://www.cabletv.com": "#fc4146",
        "https://www.highspeedinternet.com": "#14234c",
        "https://www.satelliteinternet.com": "#1b96cc",
        "https://www.reviews.org": "#e86223",
        "https://www.whistleout.com": "#0099ff",
    };

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
                },
                ...Object.keys(domainWiseClicks[0]).map(function(domain) {
                    var domainData = domainWiseClicks.map(function(data) {
                        return data[domain] || 0;
                    });

                    return {
                        label: domain,
                        data: domainData,
                        borderColor: domainColors[domain] || '#' + Math.floor(Math.random() * 16777215).toString(16), // Use predefined color or fallback to random color
                        fill: false,
                    };
                })
            ]
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
        var div1 = document.getElementById('dateDiv1');
        var div2 = document.getElementById('dateDiv2');
        var clicksPace = document.getElementById('clicksPace');
        var invoicePace = document.getElementById('invoicePace');


        function updateVisibility() {
            var selectedValue = filter.value;

            if (selectedValue === 'custom') {
                div1.style.display = 'block';
                div2.style.display = 'block';
            } else {
                div1.style.display = 'none';
                div2.style.display = 'none';
            }


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
<script>
    $(document).ready(function() {
        // Function to handle toggle state change
        $('[data-toggle]').on('click', function() {
            var partnerId = $(this).data('partner-id');
            var toggleId = $(this).data('toggle');
            var icon = $(this).find('i');
            var newState = icon.hasClass('fa-toggle-on') ? false : true; // Toggle state

            // Change the icon based on the new state
            if (newState) {
                icon.removeClass('fa-toggle-off').addClass('fa-toggle-on');
            } else {
                icon.removeClass('fa-toggle-on').addClass('fa-toggle-off');
            }
            $.ajax({
                url: '/update-toggle', // The route to handle the update
                method: 'POST',
                data: {
                    partner_id: partnerId,
                    toggle_id: toggleId,
                    state: newState ? 1 : 0,
                    _token: '{{ csrf_token() }}' // CSRF token for security
                },
                success: function(response) {
                    console.log('Toggle updated');
                },
                error: function(xhr) {
                    console.log('Error updating toggle');
                    console.log(xhr);
                }
            });
        })
    });
</script>
@endsection
@endsection