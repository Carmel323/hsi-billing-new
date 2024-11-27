@extends('layouts.outer_template')

@section('title', 'Reset Your password')

@section('content')

<div class="container d-flex justify-content-center align-items-center flex-column pt-5">
    <h4 class="text-primary mt-5 mb-3">Reset your password</h4>
    <div class="card p-4 w-100 shadow-sm border-0 rounded login-card bg-clearlink ">
        <div class="rounded p-2">

            <h6 class="mb-3 mt-3">Please check your email for a password reset link. If you don’t see it within a few minutes, be sure to check your spam folder.</h6>
            <a class="btn btn-primary px-3" href="/admin/login" type="button">Return to Login</a>
        </div>
    </div>
</div>
@section('footer')
<footer class="footer p-4 bg-clearlink w-100 d-flex justify-content-center position-fixed position-absolute text-center bottom-0 align-items-center ">
    @ Clearlink Technologies 2024
</footer>
@endsection
@endsection