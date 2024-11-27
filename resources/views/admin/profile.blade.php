@extends('layouts.admin_template')

@section('content')
<div class="inner">
    <div class="mb-4 w-100 mt-4">
        <h2 class="mt-2 ">Profile</h2>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card w-100 border-0 rounded mb-3 bg-clearlink">
                <div class="card-body">
                    <p class="mb-3 ms-2"><i class="fa fa-user" aria-hidden="true"></i> <strong>{{ $admin->admin_name ?? '' }}</strong></p>
                    <p class="mb-3 ms-2"><i class="fa fa-envelope me-1" aria-hidden="true"></i>{{ $admin->email ?? '' }}</p>
                </div>
            </div>
            <div class="card border-0  mb-5 rounded  p-3 bg-clearlink">
                <p class="fs-6 fw-bold">Change Password</p>
                <form action="/admin/update-password" method="post">
                    @csrf
                    <input type="password" placeholder=" Current Password" name="current_password" class="form-control mb-3" />
                    <input type="password" placeholder=" New Password" name="new_password" class="form-control" />
                    <span class="text-danger ">@error('new_password'){{ $message }} @enderror</span>
                    <input type="password" placeholder=" Confirm New Password" name="confirm_new_password" class="form-control mb-3 mt-3" />
                    <input type="submit" class="btn btn-primary btn-sm" value="Update Password">
                </form>
                <div class=" mt-4">
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
</div>
@endsection