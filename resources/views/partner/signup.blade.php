@extends('layouts.outer_template')

@section('title', 'Sign Up')

@section('button-content')
<a href="/login" style="width:75px; height:38px;" class="mt-4 mb-1 me-5 text-center btn btn-primary">Login</a>
@endsection

@section('content')

<div class="container d-flex justify-content-center align-items-center flex-column mb-5">
    <h3 class="text-primary mt-5 mb-3">Clearlink ISP Partner Program Signup</h3>
    <div class="card p-4 w-100 shadow-sm border-0 rounded signup-card bg-clearlink ">
        <div class="rounded p-2">

            <form action="/signup-partner" method="post" enctype="multipart/form-data">
                @csrf
                <h4 class=" mb-4">Company Details</h4>
                <div class="mb-3 row ">
                    <div class="col-lg">
                        <label for="company_name" class="form-label fw-bold">Company Name*</label>
                        <input name="company_name" class=" form-control" placeholder="Enter Company Name" value="{{ old('company_name') }}">
                        <span class="text-danger">@error('company_name'){{ $message }} @enderror</span>
                    </div>
                    <div class="col-lg">
                        <label for="tax_number" class="form-label fw-bold">EIN ID*</label>
                        <input name="tax_number" class="form-control" placeholder="Enter EIN ID" value="{{ old('tax_number') }}">
                        <span class="text-danger">@error('tax_number'){{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col-lg">
                        <label for="address" class="form-label fw-bold">Address*</label>
                        <input name="address" class=" form-control" placeholder="Enter Address" value="{{ old('address') }}">
                        <span class="text-danger">@error('address'){{ $message }} @enderror</span>
                    </div>
                    <div class="col-lg">
                        <label for="city" class="form-label fw-bold">City*</label>
                        <input name="city" class=" form-control" placeholder="Enter City" value="{{ old('city') }}">
                        <span class="text-danger">@error('city'){{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col-lg">
                        <label for="state" class="form-label fw-bold">State*</label>
                        <input name="state" class=" form-control" placeholder="Enter State" value="{{ old('state') }}">
                        <span class="text-danger">@error('state'){{ $message }} @enderror</span>
                    </div>
                    <div class="col-lg">
                        <label for="zip_code" class="form-label fw-bold">Zip Code*</label>
                        <input name="zip_code" class=" form-control" placeholder="Enter Zip Code" value="{{ old('zip_code') }}">
                        <span class="text-danger">@error('zip_code'){{ $message }} @enderror</span>
                    </div>
                </div>
                <hr class="">
                <h4 class=" mb-4">Primary Contact</h4>
                <div>
                    <div class=" mb-3 row">
                        <div class="col-lg">
                            <label for="first_name" class="form-label fw-bold">First Name*</label>
                            <input name="first_name" class=" form-control" placeholder="Enter First Name" value="{{ old('first_name') }}">
                            <span class="text-danger">@error('first_name'){{ $message }} @enderror</span>
                        </div>
                        <div class="col-lg">
                            <label for="last_name" class="form-label fw-bold">Last Name*</label>
                            <input name="last_name" class=" form-control" placeholder="Enter Last Name" value="{{ old('last_name') }}">
                            <span class="text-danger">@error('last_name'){{ $message }} @enderror</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <div class="col-lg">
                        <label for="email" class="form-label fw-bold">Email*</label>
                        <input name="email" class=" form-control" placeholder="Enter Email" value="{{ old('email') }}">
                        <span class="text-danger">@error('email'){{ $message }} @enderror</span>
                    </div>
                    <div class="col-lg">
                        <label for="phone_number" class="form-label fw-bold">Phone Number*</label>
                        <input name="phone_number" class=" form-control" placeholder="Enter Phone Number" value="{{ old('phone_number') }}">
                        <span class="text-danger">@error('phone_number'){{ $message }} @enderror</span>
                    </div>
                </div>
                <input name="country" class="mb-3 form-control" value="United States" hidden>
                <p>By clicking Sign Up you agree to the <a href="/terms-conditions" target="_blank" class="mt-2">terms and conditions</a>.</p>
                <input type="submit" class="btn btn-primary rounded mt-2 " value="Sign Up" />
            </form>
        </div>
    </div>
</div>
@section('footer')
<footer class="footer p-4 bg-clearlink w-100 d-flex justify-content-center text-center bottom-0 align-items-center ">
    @ Clearlink Technologies 2024
</footer>
@endsection

@endsection