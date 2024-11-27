@extends('layouts.view-partner-template')

@section('child-content')

<!-- <div style="width:80%" class="d-flex flex-row justify-content-between mt-5">
    <div>
        <h5 class="fw-bold">Clicks Data</h5>
    </div>
</div> -->

<div style="width:80%" class="top-row mt-4">
    <div class="row">
        <div class="container">
            <div class="d-flex justify-content-between">
                <h5 class="fw-bold">Clicks Data</h5>
                <div>
                    <a type="button" class="text-secondary fs-4 me-4" data-toggle="tooltip" data-bs-placement="bottom" title="Filter" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                        <i class="fa-solid fa-filter"></i>
                    </a>
                    <a href="{{ route('view.partner.reports.export', ['id' => $partner->id,'filter' => $filter, 'data_split' => $dataSplit], false) }}" data-bs-placement="bottom" data-toggle="tooltip" title="Download CSV" class="text-secondary fs-4 me-5"><i class="fa-solid fa-download"></i></a>



                    <div class="modal fade " id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog  modal-lg ">
                            <div class="modal-content bg-popup">
                                <div class="modal-header d-flex justify-content-between border-0 bg-popup mb-0">
                                    <h3 class="fw-bold ">Filter By </h3>
                                    <button type="button" class="close border-0 bg-popup" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid text-dark fa-xmark fs-4"></i></button>
                                </div>
                                <div class="modal-body m-0 p-0">

                                    <form method="GET" action="{{ route('view.partner.clicksdata', ['id' => $partner->id], false) }}" class="mb-3">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="filter" class="fw-bold">Time Period</label>
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

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="date_from" class="fw-bold">From Date</label>
                                                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{$dateFrom->format('Y-m-d')}}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="date_to" class="fw-bold">To Date</label>
                                                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo->format('Y-m-d') }}">
                                                </div>
                                            </div>


                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="data_split" class="fw-bold">Show data by</label>

                                                    <select name="data_split" id="data_split" class="form-control">
                                                        <option value="daily" {{ $dataSplit == 'daily' ? 'selected' : '' }}>Day</option>
                                                        <option value="weekly" {{ $dataSplit == 'weekly' ? 'selected' : '' }}>Week</option>
                                                        <option value="monthly" {{ $dataSplit == 'monthly' ? 'selected' : '' }}>Month</option>
                                                    </select>

                                                </div>
                                            </div>


                                        </div>
                                        <div class="col-md-1">
                                            <label for=""></label>
                                            <button type="submit" class="btn  button-clearlink text-primary fw-bold ">Apply</button>
                                        </div>
                                    </form>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <p class="fw-bold m-0 mb-1">Date</p>

            <div style="width:300px;" class="border border-primary bg-clearlink text-center p-1 mb-4 rounded">{{$startingDate}} - {{$endDate}}</div>

            <div class="row mx-1">
                <div class="border col-lg me-2 shadow mb-2">
                    <div class="d-flex justify-content-center align-items-center p-1 ">
                        <div class="column">
                            <h1 class="text-center">{{$total_clicks}}</h1>
                            <h6 class=" mb-2 text-body-secondary text-center">Tune Clicks</h6>
                        </div>
                    </div>
                </div>
                <div class="border col-lg me-2 shadow mb-2">
                    <div class="d-flex justify-content-center align-items-center  p-1">
                        <div class="column">
                            <h1 class="text-center">-</h1>
                            <h6 class=" mb-2 text-body-secondary text-center">Conversion Rate</h6>
                        </div>
                    </div>
                </div>
                <div class="border col-lg me-2 shadow mb-2">
                    <div class="d-flex justify-content-center align-items-center  p-1">
                        <div class="column">
                            <h1 class="text-center">-</h1>
                            <h6 class=" mb-2 text-body-secondary text-center">Cost</h6>
                        </div>

                    </div>
                </div>
                <div class="border col-lg me-2 shadow mb-2">
                    <div class="d-flex justify-content-center align-items-center  p-1">
                        <div class="column">
                            <h1 class="text-center">-</h1>
                            <h6 class=" mb-2 text-body-secondary">Click Price</h6>
                        </div>
                    </div>
                </div>
                <div class="border col-lg me-2 shadow mb-2">
                    <div class="d-flex justify-content-center align-items-center  p-1">
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
    </div>
</div>


<div class="mt-4 partner-card">
    <canvas id="clicksChart"></canvas>
</div>


@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script>
    var ctx = document.getElementById('clicksChart').getContext('2d');
    var canvas = document.getElementById('clicksChart');
    canvas.style.width = '100%';
    canvas.style.height = '300px';
    var data = @json($chartData);
    var affiliate_domains = @json($affiliate_domains);
    var labels = [];
    var datasets = [];

    var colors = [
        'rgba(13, 110, 253, 0.2)',
        'rgba(255, 99, 132, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(255, 206, 86, 0.2)',
        'rgba(75, 192, 192, 0.2)',

    ];


    affiliate_domains.forEach(function(domain, index) {
        var clickCounts = [];
        data[domain].forEach(function(clickData) {

            if (!labels.includes(clickData.click_date)) {
                labels.push(clickData.click_date);
            }
            clickCounts.push(clickData.total_clicks);
        });

        datasets.push({
            label: domain,
            data: clickCounts,
            backgroundColor: colors[index % colors.length],
            borderColor: colors[index % colors.length].replace(/0.2/, '1'),
            borderWidth: 1
        });
    });


    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterSelect = document.getElementById('filter');
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');

        function resetFilter() {
            filterSelect.value = '-';
        }

            dateFromInput.addEventListener('change', resetFilter);
        dateToInput.addEventListener('change', resetFilter);
    });
</script>
@endsection



@endsection