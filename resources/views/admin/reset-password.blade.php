@extends('layouts.outer_template')

@section('title', 'Reset your password')

@section('content')

<div class="container d-flex justify-content-center align-items-center flex-column pt-5">
    <h4 class="text-primary mt-5 mb-3">Reset your password</h4>
    <div class="card p-4 w-100 shadow-sm border-0 rounded login-card bg-clearlink">
        <div class="rounded p-2">

            <form action="/admin/forgot-password" method="post">
                @csrf
                <div class="mb-3 fw-bold">
                    Please provide the verified email address associated with your user account, and we will send you a password reset link. </div>
                <div class="form-group mb-3">
                    <input type="email" class="form-control mt-2 border-dark shadow-none" placeholder="Enter Email" name="email" value="{{ old('email') }}">
                    <span class="text-danger">@error('email'){{ $message }} @enderror</span>
                </div>
                <div class="form-group mb-3 d-flex justify-content-start align-items-center">
                    <button class="btn btn-primary" type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@section('footer')
<footer class="footer p-4 bg-clearlink w-100 d-flex justify-content-center position-fixed position-absolute text-center bottom-0 align-items-center ">
    @ Clearlink Technologies 2024
</footer>
@endsection
@endsection