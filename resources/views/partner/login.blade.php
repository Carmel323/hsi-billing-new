@extends('layouts.outer_template')

@section('title', 'Login')

@section('button-content')
<!-- <a href="/signup" style="width:75px; height:38px;" class="mt-4 mb-1 me-5 text-center btn btn-primary">SignUp</a> -->
@endsection

@section('content')

<div class="container d-flex justify-content-center align-items-center flex-column mt-5">
    <h3 class="text-primary mt-5 mb-3">Clearlink ISP Partner Program</h3>
    <div class="card p-4 w-100 shadow-sm border-0 rounded login-card bg-clearlink ">
        <div class="rounded p-2">

            <form action="/login-user" method="post">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label fw-bold">Email address</label>
                    <input type="email" class="form-control shadow-none" id="email" placeholder="Enter Email" name="email" value="{{ old('email') }}">
                    <span class="text-danger">@error('email'){{ $message }} @enderror</span>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label fw-bold">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" class="form-control border-end-0 shadow-none" placeholder="Enter Password" name="password">
                        <span class="input-group-text bg-white border-start-0" style="cursor: pointer;">
                            <i class="fas fa-eye password-toggle-icon"></i>
                        </span>
                    </div>
                    <span class="text-danger">@error('password'){{ $message }} @enderror</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                    <button class="btn btn-primary" type="submit">Login</button>
                    <a href="/reset-password" class="text-decoration-none text-primary">Forgot password?</a>
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