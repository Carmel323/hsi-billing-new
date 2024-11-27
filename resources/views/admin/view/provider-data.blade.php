@extends('layouts.view-partner-template')

@section('child-content')

<div style="width:80%" class="d-flex flex-row justify-content-between mt-5">
    <div>
        <h5 class="fw-bold">Company Info</h5>
    </div>
    <div>
        @if($data === null)
        <a data-bs-toggle="modal" data-bs-target="#uploadLogoModal" style="cursor:pointer;" class="fw-bold btn btn-primary btn-sm">Upload Company Info</a>
        @endif
    </div>
</div>

<div class="card partner-card border border-dark ms-0 m-2 mb-5">
    <div class="card-body table-responsive p-0">

        @if($data)
        <table class="text-center mt-1 m-0 table border-dark rounded">
            <thead>
                <tr>
                    <th class="fw-normal">Logo Image</th>
                    <th class="fw-normal">Landing Page Url</th>
                    <th class="fw-normal">Landing Page Url(Spanish)</th>
                    <th class="fw-normal">Company Name</th>
                </tr>
            </thead>

            <tbody>
                <tr class="py-3 text-center ">
                    <td class="text-primary">

                        <div class="d-flex flex-row align-items-center"><img src="{{$url}}" alt="logo" style="width:125px;" />
                            <a class="btn btn-primary btn-sm ms-3" style="height:35px;" href="/download/{{ $data->logo_image }}" target="_blank">Download</a>
                        </div>
                    </td>
                    <td class="fw-bold">{{$data->landing_page_url}}</td>
                    <td class="fw-bold">{{$data->landing_page_url_spanish}}</td>
                    <td class="fw-bold">{{$data->company_name}}</td>
                </tr>
            </tbody>
        </table>
        @else
        <div class="d-flex justify-content-center align-items-center m-5  ">
            <p class="m-0 p-0">No data found</p>
        </div>
        @endif
    </div>
</div>
<div style="width:80%" class="d-flex flex-row justify-content-between mt-5">
    <div>
        <h5 class="fw-bold">Provider Data</h5>
    </div>
    <div>

        <a data-bs-toggle="modal" data-bs-target="#uploadCSVModal" style="cursor:pointer;" class="btn btn-sm btn-primary fw-bold">Upload Provider Data</a>
    </div>
</div>

<div style="width:80%" class="top-row mb-4 mt-4">
    <div class="row">
        <div class="col-md-11">
            <form action="{{ route('view.partner.providerdata',['id' => $partner->id],false) }}" method="GET" class="row g-3 align-items-center w-100">
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
        <form action="{{ route('view.partner.providerdata',['id' => $partner->id],false) }}" method="GET" class="row g-3 align-items-center w-100">
            <div class="col-md-1">
                <div class="input-group mt-2">
                    <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card partner-card border border-dark ms-0 m-2 ">
    <div class="card-body table-responsive p-0">

        @if ($availability_data->isNotEmpty())

        <table class="text-center mt-1 m-0 table border-dark  rounded">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>File Name</th>
                    <th>File Size</th>
                    <th>ZIP Count</th>
                    <th>CSV File URL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($availability_data as $data)
                <tr>
                    <td>{{$data->created_at }}</td>
                    <td>{{ $data->file_name }}</td>
                    <td>{{ round($data->file_size/1024,2) }} KB</td>
                    <td>{{ $data->zip_count }}</td>
                    <td>
                        <a class="btn btn-primary btn-sm ms-3 download-button" target="_blank" href="/download/{{$data->url}}">Download</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="d-flex justify-content-center align-items-center m-5  ">
            <p class="m-0 p-0">No availability data found</p>
        </div>
        @endif
    </div>
    <div class="modal fade" id="uploadLogoModal" tabindex="-1" aria-labelledby="uploadLogoModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-popup">
                <div class="modal-header">
                    <h3 class="modal-title">Enter the required details</h3>
                    <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                </div>
                <div class="modal-body">
                    <form action="/upload-provider-data" method="post" enctype="multipart/form-data">
                        @csrf
                        <label for="logo" class="form-label fw-bold">Logo*</label>
                        <input type="file" name="logo" class="form-control mb-3" accept="image/*" />
                        <label for="landing_page_url" class="form-label fw-bold">Landing Page URL*</label>
                        <input name="landing_page_url" class=" form-control" required>
                        <label for="company_name" class="form-label fw-bold">Company Name*</label>
                        <input name="company_name" class=" form-control" required>
                        <input type="text" name="partner_id" value="{{$partner->id}}" hidden>
                        <button type="submit" class="btn btn-primary mt-3 mb-2">Upload Company Info</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="uploadCSVModal" tabindex="-1" aria-labelledby="uploadCSVModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-popup">
                <div class="modal-header">
                    <h3 class="modal-title">Enter the required details</h3>
                    <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                </div>
                <div class="modal-body">
                    <form action="/upload-provider-availability-data" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="text" name="partner_id" value="{{$partner->id}}" hidden>
                        <input type="file" class="form-control" name="csv_file" accept=".csv">
                        <button type="submit" class="btn btn-primary mt-3 mb-2">Upload Provider Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class=" mt-2 mb-5 paginate">
    <div class="row">
        @if ($availability_data->isNotEmpty())
        <div class="col-lg mt-4">
            Total Count: <strong>{{$totalCount}}</strong>
        </div>
        @endif
        <div class="pagination col-lg m-0">
            @if ($availability_data->lastPage() > 1)
            <ul class="pagination">
                <li class="{{ ($availability_data->currentPage() == 1) ? 'disabled' : '' }}">
                    <a href="{{ $availability_data->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
                </li>
                @for ($i = 1; $i <= $aoa_data->lastPage(); $i++)
                    <li class="{{ ($availability_data->currentPage() == $i) ? 'active' : '' }}">
                        <a href="{{ $availability_data->appends(request()->query())->url($i) }}" class="page-link{{ ($availability_data->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
                    </li>
                    @endfor
                    <li class="{{ ($availability_data->currentPage() == $availability_data->lastPage()) ? 'disabled' : '' }}">
                        <a href="{{ $availability_data->appends(request()->query())->url($availability_data->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
                    </li>
            </ul>
            @endif
        </div>
    </div>


</div>


@endsection