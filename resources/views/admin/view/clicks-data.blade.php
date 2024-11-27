@extends('layouts.view-partner-template')

@section('child-content')

<div style="width:80%" class="d-flex flex-row justify-content-between mt-5">
    <div>
        <h5 class="fw-bold">Clicks Data</h5>
    </div>
</div>

<div style="width:80%" class="top-row mt-4">
    <div class="row">
        <div class="col-md-11">
            <form method="GET" action="{{ route('view.partner.clicksdata', ['id' => $partner->id], false) }}" class="mt-3 mb-3">
                <div class="row">

                    <div class="col-md">
                        <div class="form-group">
                            <label for="date_from">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="form-group">
                            <label for="date_to">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
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

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="data_split">Show data by</label>

                            <select name="data_split" id="data_split" class="form-control">
                                <option value="monthly" {{ $dataSplit == 'monthly' ? 'selected' : '' }}>Month</option>
                                <option value="weekly" {{ $dataSplit == 'weekly' ? 'selected' : '' }}>Week</option>
                                <option value="daily" {{ $dataSplit == 'daily' ? 'selected' : '' }}>Day</option>
                            </select>

                        </div>
                    </div>
                    <div class="col-md-3 form-group mt-3">
                        <label for="affiliate_ids">Filter by Affiliate IDs:</label>

                        <div class="custom-dropdown-filter">
                            <div class="custom-dropdown-button rounded">
                                <div class="tags-container"><input type="hidden" id="hiddenSelect" name="affiliate_ids[]" /></div>
                                <i class="fas fa-chevron-down dropdown-icon text-secondary"></i>
                            </div>
                            <div class="custom-dropdown-content">
                                @foreach($affiliates as $affiliate)
                                <option class="custom-option p-1" data-value="{{$affiliate->isp_affiliate_id}}({{$affiliate->domain_name}})" {{ in_array($affiliate->isp_affiliate_id, $affiliateIdsArray) ? 'selected' : '' }}>{{$affiliate->isp_affiliate_id}}({{$affiliate->domain_name}})</option>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mt-3">
                        <label for=""></label>
                        <button type="submit" class="btn  button-clearlink text-primary fw-bold mt-4 ms-4">Apply</button>
                    </div>
                    <div class="col-md-2 mt-3">
                        <a href="{{ route('view.partner.reports.export', ['id' => $partner->id,'filter' => $filter, 'data_split' => $dataSplit], false) }}" class="btn btn-primary btn-sm mt-4">Download CSV</a>
                    </div>
                </div>
            </form>
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
<script>
    const dropdownButton = document.querySelector(".custom-dropdown-button");
    const dropdownContent = document.querySelector(
        ".custom-dropdown-content"
    );
    const tagsContainer = dropdownButton.querySelector(".tags-container");
    const buttonText = dropdownButton.querySelector(".button-text");
    const hiddenSelect = document.getElementById("hiddenSelect");

    window.onload = function() {
        var affiliateValues = @json($values);
        affiliateValues.forEach(function(value) {
            if (value) {
                addTag(value);
                const divs = dropdownContent.querySelectorAll('option');
                divs.forEach(div => {
                    if (div.innerText.trim() === value.trim()) {
                        div.remove();
                    }
                });
            }

        });
    };


    dropdownButton.addEventListener("click", () => {
        const isVisible = dropdownContent.style.display === "block";
        dropdownContent.style.display = isVisible ? "none" : "block";
    });

    document.addEventListener("click", (event) => {
        if (
            !dropdownButton.contains(event.target) &&
            !dropdownContent.contains(event.target)
        ) {
            dropdownContent.style.display = "none";
        }
    });

    dropdownContent.addEventListener("click", (event) => {
        const selectedDiv = event.target;
        if (selectedDiv && selectedDiv.dataset.value) {
            const value = selectedDiv.dataset.value;

            // Create a tag element
            addTag(value);
            selectedDiv.remove();

            // Hide the dropdown content
            dropdownContent.style.display = "none";
        }
    });


    function addTag(value) {
        const tag = document.createElement("div");
        tag.classList.add("tag-filter");
        tag.innerHTML = `${value} <span class="remove-tag-filter">&times;</span>`;

        // Append the tag to the container
        tagsContainer.appendChild(tag);

        const currentValues = hiddenSelect.value ? hiddenSelect.value.split(',') : [];
        if (!currentValues.includes(value)) {
            currentValues.push(value);
            hiddenSelect.value = currentValues.join(',');
        }

    }
    tagsContainer.addEventListener("click", (event) => {
        if (event.target.classList.contains("remove-tag-filter")) {
            const tagToRemove = event.target.parentElement;

            tagsContainer.removeChild(tagToRemove);

            const valueToRemove = tagToRemove.textContent.trim().slice(0, -1); // Remove the '×'

            const items = hiddenSelect.value.split(',');

            const filteredItems = items.filter(item => item.trim() !== valueToRemove.trim());

            hiddenSelect.value = filteredItems.join(',');

            var newOption = document.createElement('option');

            newOption.setAttribute('data-value', valueToRemove);
            newOption.textContent = valueToRemove;
            newOption.className = "custom-option p-1";


            dropdownContent.appendChild(newOption);


            if (tagsContainer.children.length === 0) {
                buttonText.textContent = "Select an option";
            }

        }
    });
</script>
@endsection



@endsection