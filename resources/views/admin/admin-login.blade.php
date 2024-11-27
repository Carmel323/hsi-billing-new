@extends('layouts.outer_template')

@section('title', 'Admin Login')

@section('content')
<div class="d-flex flex-column justify-content-center align-items-center pt-5">
    <h4 class="text-primary mt-5 mb-3">Clearlink ISP Billing Admin Portal</h4>
    <div class="card p-4 w-100 shadow-sm border-0 rounded login-card bg-clearlink">


        <form action="/login-admin" method="post">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control shadow-none" id="email" placeholder="Enter Email" name="email" value="{{ old('email') }}">
                <span class="text-danger">@error('email'){{ $message }} @enderror</span>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" id="password" class="form-control border-end-0 shadow-none" placeholder="Enter Password" name="password">
                    <span class="input-group-text bg-white border-start-0" style="cursor: pointer;">
                        <i class="fas fa-eye password-toggle-icon"></i>
                    </span>
                </div>
                <span class="text-danger">@error('password'){{ $message }} @enderror</span>
            </div>
            <div class="form-group mb-3 d-flex justify-content-between align-items-center">
                <button class="btn btn-primary" type="submit">Login</button>
                <a href="/admin/reset-password" class="fs-6 text-decoration-none text-primary">Forgot password?</a>
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

@push('scripts')
<script>
    const passwordField = document.getElementById("password");
    const togglePassword = document.querySelector(".password-toggle-icon");

    togglePassword.addEventListener("click", function() {
        if (passwordField.type === "password") {
            passwordField.type = "text";
            togglePassword.classList.remove("fa-eye");
            togglePassword.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            togglePassword.classList.remove("fa-eye-slash");
            togglePassword.classList.add("fa-eye");
        }
    });
</script>
@endpush