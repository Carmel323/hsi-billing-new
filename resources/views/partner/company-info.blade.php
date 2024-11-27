@extends('layouts.partner_template')
@section('content')
<div>
    <div class="mb-2 w-100">
        <h2 class="mt-2 mb-5">Company Info</h2>
    </div>
    @if($provider_data)
    <form action="/update-provider-data" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4 row">
            <div class="col-lg-2">
                <label for="logo" class="form-label fw-bold ">Logo*</label>
                <img src="{{ $url }}" alt="logo" style="width:125px;" />
            </div>
            <div class="col-lg-6">
                <label for="uploadImage" class="image-upload-btn text-primary fw-bold">Update Logo</label>
                <input type="file" id="uploadImage" name="logo" accept="image/*" />
                <span class="text-danger"> @error('logo'){{ $message }} @enderror</span>
            </div>
        </div>
        <div class="mb-4 row">

        </div>

        <div class="row">
            <div class="col-lg-4">
                <label for="landing_page_url" class="form-label fw-bold">Landing Page Url* <span class="body-text-small fw-normal mt-1 ms-2">(The page where you want customers to land on your site.)</span></label>
                <input name="landing_page_url" class=" form-control" value="{{$provider_data->landing_page_url}}" required>
                <span class="text-danger"> @error('landing_page_url'){{ $message }} @enderror</span>
                <p class="body-text-small mt-1">Enter the full URL, including 'http://' or 'https://'. For example, 'https://www.example.com'.</p>
            </div>
            <div class="col-lg-4 mb-4 ">
                <label for="landing_page_url_spanish" class="form-label fw-bold">Landing Page Url(Spanish)<span class="body-text-small fw-normal mt-1 ms-2">(The page where you want Spanish-speaking customers to land on your site.)</span></label>
                <input name="landing_page_url_spanish" class=" form-control" value="{{$provider_data->landing_page_url_spanish}}">
                <span class="text-danger"> @error('landing_page_url_spanish'){{ $message }} @enderror</span>
                <p class="body-text-small mt-1">Enter the full URL, including 'http://' or 'https://'. For example, 'https://www.example.com'.</p>
            </div>

        </div>
        <div class="mb-4 row">
            <div class="col-lg-4">
                <label for="company_name" class="form-label fw-bold">Company name for display on site*</label>
                <input name="company_name" class=" form-control" value="{{$provider_data->company_name}}" required>
                <span class="text-danger"> @error('company_name'){{ $message }} @enderror</span>
            </div>

            <!-- <div class="col-lg-4 mb-4 ">
                <label for="tune_link" class="form-label fw-bold">Tune Link*</label>
                <input name="tune_link" value="{{$provider_data->tune_link}}" class=" form-control" required>
                <span class="text-danger"> @error('tune_link'){{ $message }} @enderror</span>
            </div> -->


        </div>
        <input type="submit" class="btn btn-primary text-end text-white px-3" value="Update Data">
        <!-- <a class="btn button-clearlink text-primary fw-bold ms-3" data-bs-toggle="modal" data-bs-target="#sendDetailModal"> Send Details To Admin</a> -->
    </form>
    <div class="modal fade" id="sendDetailModal" tabindex="-1" aria-labelledby="sendDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content bg-popup">
                <div class="modal-header">
                    <h3 class="modal-title " id="addressUpdateModalLabel">Send Details</h3>
                    <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                </div>
                <div class="modal-body">
                    <form action="/send-details" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-lg">
                                <label class="fw-bold">Select Admins*</label>
                                <select name="admin_id" class="form-select">
                                    @foreach($admins as $admin)
                                    <option value="{{$admin->id}}">{{$admin->admin_name}}</option>
                                    @endforeach
                                </select>
                                <input value="{{$partner->zoho_cust_id}}" name="partner_id" hidden />
                            </div>

                        </div>
                        <input type="submit" class="btn btn-primary px-3 py-2 rounded popup-element" value="Send Details">
                    </form>
                </div>
            </div>
        </div>
    </div>
    @else
    <form action="/save-provider-data" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4 row">
            <div class="col-lg-6">
                <label for="uploadImage" class="image-fresh-upload-btn text-primary fw-bold">Upload logo</label>
                <input type="file" id="uploadImage" name="logo" accept="image/*" />
                <img src="#" id="previewImage" alt="Preview" style="max-width: 100%; max-height: 70px; display: none;">
                <span class="text-danger"> @error('logo'){{ $message }} @enderror</span>
                <p class="body-text-small mt-1">Required</p>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-4 mb-4">
                <label for="landing_page_url" class="form-label fw-bold">Landing Page URL*<span class="body-text-small fw-normal mt-1 ms-2">(The page where you want customers to land on your site.)</span></label>
                <input name="landing_page_url" class=" form-control" required>
                <span class="text-danger"> @error('landing_page_url'){{ $message }} @enderror</span>
                <p class="body-text-small mt-1">Enter the full URL, including 'http://' or 'https://'. For example, 'https://www.example.com'.</p>
            </div>
            <div class="col-lg-4 mb-4 ">
                <label for="landing_page_url_spanish" class="form-label fw-bold">Spanish Landing Page URL(if applicable)<span class="body-text-small fw-normal mt-1 ms-2">(The page where you want Spanish-speaking customers to land on your site.)</span> </label>
                <input name="landing_page_url_spanish" class=" form-control">
                <span class="text-danger"> @error('landing_page_url_spanish'){{ $message }} @enderror</span>
                <p class="body-text-small mt-1">Enter the full URL, including 'http://' or 'https://'. For example, 'https://www.example.com'.</p>
            </div>

        </div>
        <div class=" row">
            <div class="col-lg-4 mb-4">
                <label for="company_name" class="form-label fw-bold">Company name to display on site*</label>
                <input name="company_name" value="{{$partner->company_name}}" class=" form-control" required>
                <span class="text-danger"> @error('company_name'){{ $message }} @enderror</span>
            </div>

            <!-- <div class="col-lg-4 mb-4 ">
                <label for="tune_link" class="form-label fw-bold">Tune Link*</label>
                <input name="tune_link" class=" form-control" required>
                <span class="text-danger"> @error('tune_link'){{ $message }} @enderror</span>
            </div> -->
        </div>

        <input type="submit" class="btn btn-primary text-end text-white px-3 " value="Save">

    </form>


    @endif
</div>

@include('layouts.show-alert-modal')
@endsection