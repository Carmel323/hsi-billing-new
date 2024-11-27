@extends('layouts.outer_template')

@section('title', 'Change Password')

@section('content')

<div class="container d-flex justify-content-center align-items-center flex-column pt-2 mt-4">
    <h4 class="text-primary mb-3">Clearlink ISP Partner Portal Change Password</h4>
    <div style="margin-bottom: 100px;" class="card p-4 w-100 shadow-sm border-0 rounded login-card bg-clearlink">
        <div class="rounded p-2">

            <form action="/admin/reset-password" method="post">
                @csrf
                <input type="text" value="{{$token}}" name="token" hidden />
                <div class="form-group mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="border border-dark rounded d-flex flex-row align-items-center">
                        <input type="password" id="password1" class="form-control border-0 shadow-none" name="password">
                        <span style="cursor: pointer;" class=" me-2"><i class="password-toggle-icon1 fas fa-eye"></i></span>
                    </div>
                    <span class="text-danger">@error('password'){{$message}} @enderror</span>
                </div>
                <div class="form-group mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="border border-dark rounded d-flex flex-row align-items-center">
                        <input type="password" id="password" class="form-control border-0 shadow-none" name="confirm_password">
                        <span style="cursor: pointer;" class=" me-2"><i class=" password-toggle-icon fas fa-eye"></i></span>
                    </div>
                    <span class="text-danger">@error('password'){{$message}} @enderror</span>
                </div>
                <div class="form-group mb-3 d-flex justify-content-start align-items-center">
                    <button class="btn btn-primary" type="submit">Change Password</button>
                </div>
            </form>
            <div class=" mt-5">
                <p class="fs-6 fw-bold">Password Instructions</p>
                <ul id="billing" class="">
                    <li class="billing">
                        The password should have a minimum length of 6 characters
                    </li>
                    <li class="billing">
                        The password should contain at least one letter
                    </li>
                    <li class="billing">
                        The password should contain at least one number
                    </li>
                    <li class="billing">
                        The password should contain at least one symbol (special character)
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@section('footer')
<footer class="footer p-4 bg-clearlink w-100 d-flex justify-content-center text-center bottom-0 align-items-center ">
    @ Clearlink Technologies 2024
</footer>
@endsection
@endsection