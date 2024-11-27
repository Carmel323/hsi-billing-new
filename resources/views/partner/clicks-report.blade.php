@extends('layouts.partner_template')

@section('content')
@if($current_subscription && $total_clicks)
<div class="container">
    <div>
        <h2 class="mt-2">{{ $partner->company_name }} Clicks Report</h2>
    </div>

    <form method="GET" action="{{ route('partner.reports', [], false) }}" class="mt-3 mb-3">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="filter">Time Period</label>
                    <select name="filter" id="filter" class="form-control">
                        <option value="last_12_months" {{ $filter == 'last_12_months' ? 'selected' : '' }}>Last 12 Months</option>
                        <option value="last_6_months" {{ $filter == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                        <option value="last_3_months" {{ $filter == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                        <option value="last_1_month" {{ $filter == 'last_1_month' ? 'selected' : '' }}>Last 1 Month</option>
                        <option value="last_7_days" {{ $filter == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="data_split">Show data by</label>
                    @if($is_daily_plan)
                    <select name="data_split" id="data_split" class="form-control">
                        <option value="monthly" {{ $dataSplit == 'monthly' ? 'selected' : '' }}>Month</option>
                        <option value="weekly" {{ $dataSplit == 'weekly' ? 'selected' : '' }}>Week</option>
                        <option value="daily" {{ $dataSplit == 'daily' ? 'selected' : '' }}>Day</option>
                    </select>
                    @elseif($is_weekly_plan)
                    <select name="data_split" id="data_split" class="form-control">
                        <option value="monthly" {{ $dataSplit == 'monthly' ? 'selected' : '' }}>Month</option>
                        <option value="weekly" {{ $dataSplit == 'weekly' ? 'selected' : '' }}>Week</option>
                    </select>
                    @elseif($is_monthly_plan)
                    <select name="data_split" id="data_split" class="form-control">
                        <option value="monthly" {{ $dataSplit == 'monthly' ? 'selected' : '' }}>Month</option>
                    </select>
                    @endif
                </div>
            </div>
            <div class="col-md-2">
                <label for=""></label>
                <button type="submit" class="btn btn-primary btn-block mt-4">Apply</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('partner.reports.export', ['filter' => $filter, 'data_split' => $dataSplit], false) }}" class="btn btn-primary btn-block mt-4">Download CSV</a>
            </div>
        </div>
    </form>

    <div class="fw-bold">
        Total Clicks: {{$total_clicks}}
    </div>

    <div class="mt-4">
        <canvas id="clicksChart"></canvas>
    </div>

    <!--     <div class="mt-5">
        <h4>{{ $partner->company_name }} Top {{ $topN }} Zip Codes by Clicks</h4>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Zip Code</th>
                    <th>Total Clicks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($topZipCodes as $index => $zipCode)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $zipCode->intended_zip }}</td>
                    <td>{{ $zipCode->total_clicks }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div> -->
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
    var ctx = document.getElementById('clicksChart').getContext('2d');
    var data = @json($chartData);

    var labels = [];
    var clickCounts = [];

    data.forEach(function(item) {
        labels.push(item.click_date);
        clickCounts.push(item.click_count);
    });

    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Clicks',
                data: clickCounts,
                backgroundColor: 'rgb(13 110 253)',
                borderColor: 'rgb(13 110 253)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                datalabels: {
                    align: 'end',
                    anchor: 'end'
                }
            }
        }
    });
</script>
@endsection