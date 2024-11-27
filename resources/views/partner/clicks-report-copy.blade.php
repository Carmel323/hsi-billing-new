@extends('layouts.partner_template')

@section('content')
@if($current_subscription && $total_clicks)
<div class="container">
    <div class="d-flex justify-content-between">
        <h2 class="mt-2">{{ $partner->company_name }} Clicks Report</h2>
        <div>
            <a type="button" class="text-secondary fs-4 me-4" data-toggle="tooltip" data-bs-placement="bottom" title="Filter" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                <i class="fa-solid fa-filter"></i>
            </a>
            <a href="{{ route('partner.reports.export', ['filter' => $filter, 'data_split' => $dataSplit], false) }}" data-bs-placement="bottom" data-toggle="tooltip" title="Download CSV" class="text-secondary fs-4 me-5"><i class="fa-solid fa-download"></i></a>



            <div class="modal fade " id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-popup">
                        <div class="modal-header d-flex justify-content-between border-0 bg-popup mb-0">
                            <h3 class="fw-bold ">Filter By </h3>
                            <button type="button" class="close border-0 bg-popup" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid text-dark fa-xmark fs-3"></i></button>
                        </div>
                        <div class="modal-body m-0 p-0">
                            <form method="GET" action="{{ route('partner.reports', [], false) }}" class="mb-3 mt-0">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="fw-bold" for="filter">Time Period</label>
                                            <select name="filter" id="filter" class="form-control">
                                                <option value="mtd" {{ $filter == 'mtd' ? 'selected' : '' }}>MTD</option>
                                                <option value="this_month" {{ $filter == 'this_month' ? 'selected' : '' }}>This Month</option>
                                                <option value="last_12_months" {{ $filter == 'last_12_months' ? 'selected' : '' }}>Last 12 Months</option>
                                                <option value="last_6_months" {{ $filter == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                                                <option value="last_3_months" {{ $filter == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                                                <option value="last_1_month" {{ $filter == 'last_1_month' ? 'selected' : '' }}>Last 1 Month</option>
                                                <option value="last_7_days" {{ $filter == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                                                <option value="-" {{ $filter == '-' ? 'selected' : '' }}>Select Time Period</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="fw-bold" for="data_split">Show data by</label>

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
                                        <button type="submit" class="btn button-clearlink btn-block mt-4 text-primary fw-bold">Apply</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <p class="fw-bold m-0 mb-1">Date</p>

    <div style="width:300px;" class="border border-primary bg-clearlink text-center p-1 mb-4 rounded">{{$dateFrom}} - {{$dateTo}}</div>

    <div class="row mx-1">
        <div class="border col-lg me-2 shadow mb-2">
            <div class="d-flex justify-content-center align-items-center p-3 ">
                <div class="column">
                    <h1 class="text-center">{{$total_clicks}}</h1>
                    <h6 class=" mb-2 text-body-secondary text-center">Tune Clicks</h6>
                </div>
            </div>
        </div>
        <div class="border col-lg me-2 shadow mb-2">
            <div class="d-flex justify-content-center align-items-center p-3">
                <div class="column">
                    <h1 class="text-center">-</h1>
                    <h6 class=" mb-2 text-body-secondary text-center">Conversion Rate</h6>
                </div>
            </div>
        </div>
        <div class="border col-lg me-2 shadow mb-2">
            <div class="d-flex justify-content-center align-items-center p-3">
                <div class="column">
                    <h1 class="text-center">-</h1>
                    <h6 class=" mb-2 text-body-secondary text-center">Cost</h6>
                </div>

            </div>
        </div>
        <div class="border col-lg me-2 shadow mb-2">
            <div class="d-flex justify-content-center align-items-center p-3">
                <div class="column">
                    <h1 class="text-center">-</h1>
                    <h6 class=" mb-2 text-body-secondary">Clicks Pace</h6>
                </div>
            </div>
        </div>
        <div class="border col-lg me-2 shadow mb-2">
            <div class="d-flex justify-content-center align-items-center p-3">
                <div class="column">
                    <h1 class="text-center">-</h1>
                    <h6 class="text-center mb-2 text-body-secondary">Invoice Pace</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4 mb-5">
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
    var canvas = document.getElementById('clicksChart');
    canvas.style.width = '100%';
    canvas.style.height = '400px';
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