<!-- @extends('layouts.partner_template')
@section('content')
<h1 class="text-dark fw-bold mb-3">Provider Info</h1>
<div class="row">
    <div>
        @if($provider_data)
        <form action="/update-provider-data" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3 row">
                <div class="col-lg-2">
                    <div class="d-flex flex-row">
                        <div>
                            <label for="logo" class="form-label fw-bold ">Logo*</label>
                            <img src="{{$url}}" alt="logo" style="width:125px;" />
                        </div>
                        <div>
                            <button class="btn btn-primary btn-sm download-presigned-logo" data-url="{{ $provider_data->logo_image }}" data-token="{{ csrf_token() }}">Download</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <label for="uploadImage" class="image-upload-btn text-primary fw-bold">Update Logo</label>
                    <input type="file" id="uploadImage" name="new_logo" accept="image/*" />
                    <span class="text-danger"> @error('logo'){{ $message }} @enderror</span>
                </div>
            </div>
    </div>
    <div class="mb-3 row">
        <label for="landing_page_url" class="form-label fw-bold">Landing Page Url*<span class="body-text-small mt-1">(Enter the full URL, including 'http://' or 'https://'. For example, 'https://www.example.com'.)</span></label>
        <div class="col-lg-6">
            <input name="landing_page_url" class="ms-2 form-control" value="{{$provider_data->landing_page_url}}" required>
            <p class="body-text-small">"Enter the full URL, including 'http://' or 'https://'. For example, 'https://www.example.com'."</p>
            <span class="text-danger"> @error('landing_page_url'){{ $message }} @enderror</span>
        </div>
    </div>
    <div class="mb-3 row">
        <label for="landing_page_url_spanish" class="form-label fw-bold">Landing Page Url(Spanish)</label>
        <div class="col-lg-6">
            <input name="landing_page_url_spanish" class="ms-2 form-control" value="{{$provider_data->landing_page_url_spanish}}">
            <span class="text-danger"> @error('landing_page_url_spanish'){{ $message }} @enderror</span>
        </div>
    </div>
    <div class="mb-3 row">
        <label for="company_name" class="form-label fw-bold">Company name for display on site(If left blank, company name from Company Info will be used)</label>
        <div class="col-lg-6">
            <input name="company_name" class="ms-2 form-control" value="{{$provider_data->company_name}}">
            <span class="text-danger"> @error('company_name'){{ $message }} @enderror</span>
        </div>
    </div>
    <input type="submit" class="btn btn-danger text-end text-white px-3 mb-2  " value="Update Data">
    </form>
    @else
    <form action="/save-provider-data" method="POST" enctype="multipart/form-data">
        @csrf

        <div class=" row">
            <div class="col-lg-6">
                <label for="uploadImage" class="image-fresh-upload-btn text-primary fw-bold">Upload logo</label>
                <input type="file" id="uploadImage" name="logo" accept="image/*" />
                <img src="#" id="previewImage" alt="Preview" style="max-width: 100%; max-height: 100px; display: none;">
                <span class="text-danger"> @error('logo'){{ $message }} @enderror</span>
                <p class="body-text-small">Required</p>
            </div>
        </div>
        <div class="mb-3 row">
            <div class="col-lg-4">
                <label for="landing_page_url" class="form-label fw-bold">Landing Page URL*</label>
                <input name="landing_page_url" class=" form-control" required>
                <span class="text-danger"> @error('landing_page_url'){{ $message }} @enderror</span>
            </div>
            <div class="col-lg-4">
                <label for="landing_page_url_spanish" class="form-label fw-bold">Spanish Landing Page URL(if applicable)</label>
                <input name="landing_page_url_spanish" class=" form-control">
                <span class="text-danger"> @error('landing_page_url'){{ $message }} @enderror</span>
            </div>
        </div>
        <div class="mb-3 row">

        </div>
        <div class="mb-3 row">
            <label for="company_name" class="form-label fw-bold">Company name to display on site</label>
            <div class="col-lg-4">
                <input name="company_name" value="{{$partner->company_name}}" class=" form-control">
                <span class="text-danger"> @error('company_name'){{ $message }} @enderror</span>
                <p class=" body-text-small">If left blank, company name from Company Info will be used</p>
            </div>
        </div>
        <input type="submit" class="btn btn-primary text-end text-white px-3 mb-2  " value="Save">
    </form>

</div>
@endif
<div class="col-md-12">

    <h2 class="mb-3 fw-bold mt-5 mt-1">Provider availability info</h2>

    <p>Your availability information dictates the zip codes in which you’re available</p>

    <ol class="mb-3">
        <li class="numbered">Download this <span><a href="{{ asset('assets/sample/zip_list_template.csv') }}" class="text-decoration-underline">zip_list_template.csv</a></span> </li>
        <li class="numbered">
            <div class="tableTerms p-0 border-0">
                <div class="accordion accordion-flush" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button provider-accordion-button collapsed m-0 p-0  text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                <span class="text-dark button-text">Add your availability to the zip list template.</span>&nbsp;Important info for the CSV file <span class="ms-1"><i class="fa-solid fa-angle-down"></i></span>
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <ul id="billing">
                                    <li class="billing">
                                        File name can be changed. Column names and order <strong>should not</strong> be changed.
                                    </li>
                                    <li class="billing">
                                        Rows must be unique based on the combination of ZIP, Type and CustomerType.
                                        <ul>
                                            <li class="provider">A file <strong>may</strong> contain multiple rows with the same ZIP if each row has a unique Type and/or CustomerType. For example, the following is acceptable.
                                                <ul>
                                                    <li class="data">“00544, 10, Fiber, .99, Residential”</li>
                                                    <li class="data">“00544, 10, Fiber, .99, Business”</li>
                                                </ul>
                                            </li>
                                        </ul>
                                        <ul>
                                            <li class="provider">A file <strong>may not</strong> contain multiple rows with the same ZIP if the Type and/or CustomerType is not unique. For example the following <strong>is not</strong> acceptable.
                                                <ul>
                                                    <li class="data">“00544, 10, Fiber, .99, Residential”</li>
                                                    <li class="data">“00544, 5, Fiber, .74, Residential”</li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="billing">
                                        <strong>ZIP:&nbsp;</strong> Include 5 digit zip code.
                                        <ul>
                                            <li class="provider">Zips with preceding zeros can be added with or without the zeros. For example, 00544 zip code can be entered as 544.</li>
                                        </ul>
                                    </li>
                                    <li class="billing">
                                        <strong>Speed:&nbsp;</strong> Represents the download speed maximum available in that zip code. This number should be in Mbps when uploaded.
                                    </li>
                                    <li class="billing">
                                        <strong>Type:&nbsp;</strong> The technology type associated with service in the zip code. Please use the following options: 5G Home, Cable, DSL, Fiber, Fixed Wireless, LTE Home, Mobile, Other Copper Wireline, Satellite
                                    </li>
                                    <li class="billing"> <strong>Coverage:&nbsp;</strong> Percentage of the zip code area covered by the service. Use the decimal representation. For example, 100% would be 1 and 74% would be 0.74.</li>
                                    <li class="billing"> <strong>CustomerType:&nbsp;</strong> Business or Residential </li>

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </li>
        <li class="numbered">Upload the completed CSV file</li>
    </ol>


    @if ($errors->any())
    <div>
        <ul>
            @foreach ($errors->all() as $error)
            <li class="text-danger">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('upload.csv',[],false) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="mb-2 col-lg-6">
                <input type="file" class="form-control" name="csv_file" accept=".csv">
            </div>
        </div>
        <div>
            <button type="submit" class="btn btn-primary text-center text-white px-3 mb-4 mt-3">Upload availability info</button>
        </div>
    </form>

    <hr class="borders-clearlink">


    <h4 class="fw-bold mt-4">Search previous file history</h4>

    <div class="top-row w-100 mt-4">
        <div class="row">
            <div class="col-md-11">
                <form action="{{ route('partner.providerdata',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold">End Date</label>
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}" />
                        </div>
                        <div class="col-md-2">
                            <label for="per_page" class="fw-bold">Show:</label>
                            <select name="per_page" id="per_page" class="form-select">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="fw-bold">Search</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}">
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
        <div class="row">
            <form action="{{ route('partner.providerdata',[],false) }}" method="GET" class="row align-items-center w-100">
                <div class="col-md-1">
                    <div class="input-group">
                        <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($aoa_data->isNotEmpty())

    <table class="table table-bordered">
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
            @foreach ($aoa_data as $data)
            <tr>
                <td>{{ $data->created_at }}</td>
                <td>{{ $data->file_name }}</td>
                <td>{{ round($data->file_size/1024,2) }} KB</td>
                <td>{{ $data->zip_count }}</td>
                <td>
                    <a class="btn btn-primary btn-sm" href="/download/{{$data->url}}">Download</a>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-2 mb-5 paginate">
        <div class="row">
            <div class="col-lg mt-4">
                Total Count: <strong>{{$totalCount}}</strong>
            </div>

            <div class="pagination col-lg m-0">
                @if ($aoa_data->lastPage() > 1)
                <ul class="pagination">
                    <li class="{{ ($aoa_data->currentPage() == 1) ? 'disabled' : '' }}">
                        <a href="{{ $aoa_data->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
                    </li>
                    @for ($i = 1; $i <= $aoa_data->lastPage(); $i++)
                        <li class="{{ ($aoa_data->currentPage() == $i) ? 'active' : '' }}">
                            <a href="{{ $aoa_data->appends(request()->query())->url($i) }}" class="page-link{{ ($aoa_data->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
                        </li>
                        @endfor
                        <li class="{{ ($aoa_data->currentPage() == $aoa_data->lastPage()) ? 'disabled' : '' }}">
                            <a href="{{ $aoa_data->appends(request()->query())->url($aoa_data->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
                        </li>
                </ul>
                @endif
            </div>
        </div>


    </div>
    @else
    <p>No availability data found.</p>
    @endif

</div>
</div>

@endsection -->