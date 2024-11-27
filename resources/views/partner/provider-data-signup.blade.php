@extends('layouts.outer_template')
@section('title', 'Provider Data')
@section('content')

<h1 class="text-dark fw-bold mb-3 ms-5 mt-5">Provider Info</h1>
<div class="row mb-5 mx-5">
    <div>
        <form action="/register" method="POST" enctype="multipart/form-data">
            @csrf

            <div class=" row">
                <div class="col-lg-6">
                    <label for="uploadImage" class="image-fresh-upload-btn text-primary fw-bold">Upload logo</label>
                    <input type="file" id="uploadImage" name="logo" accept="image/*" required />
                    <img src="#" id="previewImage" alt="Preview" style="max-width: 100%; max-height: 70px; display: none;">
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
                    <span class="text-danger"> @error('landing_page_url_spanish'){{ $message }} @enderror</span>
                </div>
            </div>
            <div class="mb-3 row">

            </div>
            <div class="mb-3 row">
                <label for="company_name" class="form-label fw-bold">Company name to display on site</label>
                <div class="col-lg-4">
                    <input name="company_name" class=" form-control">
                    <span class="text-danger"> @error('company_name'){{ $message }} @enderror</span>
                    <p class=" body-text-small">If left blank, company name from Company Info will be used</p>
                </div>
            </div>
            <input name="email" value="{{$email}}" hidden />



    </div>

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


        @csrf
        <div class="row mt-3">
            <div class="col-lg-6">
                <h4 class="text-primary">Upload availability info </h4>
                <input type="file" class="form-control mb-4" name="csv_file" accept=".csv" required>

            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-4">Submit & Register</button>
        </form>
    </div>
</div>
@endsection
@section('footer')
<footer class="footer p-4 bg-clearlink w-100 d-flex justify-content-center text-center bottom-0 align-items-center ">
    @ Clearlink Technologies 2024
</footer>
@endsection