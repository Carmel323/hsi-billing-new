@extends('layouts.partner_template')

@section('content')
<div class="inner">
    <div class="mb-4 w-100">
        <h2 class="mt-2 ">Profile</h2>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card w-100 border-0 bg-clearlink rounded mb-3">
                <div class="card-body">
                    <h4 class="right-margin">Account Details</h4>
                    <p class="m-0 "><i class="fa fa-building right-margin text-primary" aria-hidden="true"></i> <strong>{{ $partner->company_name ?? '' }}</strong></p>


                    <p class="m-0"><i class="fa fa-user right-margin text-primary" aria-hidden="true"></i><strong> {{ $current_user->first_name ?? '' }} {{ $current_user->last_name ?? '' }}</strong></p>
                    <p class=" m-0"><i class="fa fa-envelope right-margin text-primary" aria-hidden="true"></i>{{ $current_user->email ?? '' }}</p>
                    <p class="m-0 "><i class="fa-solid fa-phone right-margin text-primary"></i>{{ $current_user->phone_number ?? '' }}</p>

                    <div class="d-flex flex-row mb-3">
                        <div class="m-0"><i class="fa fa-address-card right-margin text-primary" aria-hidden="true"></i></div>
                        <div>{{ $partner_address->street ?? '' }},<br>{{ $partner_address->city ?? '' }},<br>{{ $partner_address->state ?? '' }},<br>{{ $partner_address->country ?? '' }}<br>{{ $partner_address->zip_code ?? '' }}</div>
                    </div>
                    <div class="d-flex flex-row">
                        <a class="btn btn-primary  right-margin rounded" data-bs-toggle="modal" data-bs-target="#addressUpdateModal">Update Address</a>

                        <a class="btn text-primary text-decoration-underline fw-bold ps-0" data-bs-toggle="modal" data-bs-target="#passwordUpdateModal">Update Password</a>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card w-100 border-0 bg-clearlink rounded mb-3">
                <div class="card-body right-margin">
                    <div class="d-flex flex-row mb-5 justify-content-between">
                        <h4 class="ms-3">Users</h4>

                        <a data-bs-toggle="modal" data-bs-target="#addUserModal" class=" btn btn-primary btn-sm me-3">Invite User</a>
                    </div>



                    @if($users->isNotEmpty() )
                    @foreach($users as $index => $user)


                    @if($index > 0)
                    <hr class="borders-clearlink">
                    @endif
                    <div class="d-flex flex-row ">

                        <div class="col-lg-1 user-icon">
                            <i style="font-size: 44px;" class="fa-solid fa-circle-user text-primary"></i>
                        </div>
                        <div class="col-lg-9 ms-3">
                            <p class="p-0 m-0"><strong>{{$user->first_name}}&nbsp;{{$user->last_name}}@if($user->is_primary)<span>(Primary)</span>@endif</strong></p>
                            <p class="p-0 m-0">{{$user->email}}</p>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="d-flex justify-content-center align-items-center">
                        No secondary users found
                    </div>

                    @endif
                </div>
            </div>
        </div>

    </div>

    @if(isset($paymentmethod))
    <div class="row mb-5">
        <div class="col-lg-6">
            <div class="card w-100 border-0 bg-clearlink ">
                <div class="card-body table-responsive right-margin">
                    <h4 class="mb-3">Payment method</h4>
                    <table class="table">
                        <tbody>
                            <tr>
                                <td colspan="2" style="padding-bottom:20px;">Type</td>
                                <td>Number</td>
                                <td>Expiry Date</td>
                                <td>Status</td>
                                <td>Action</td>
                            </tr>
                            @if(isset($paymentmethod))
                            <tr>

                                <td class="pt-4">
                                    @if($paymentmethod->type == 'bank_account')
                                    <i class="fa fa-university"></i>
                                    @else
                                    <i class="fa fa-credit-card"></i>
                                    @endif

                                </td>
                                <td class="pt-4">
                                    {{ isset($paymentmethod->type) ? ucfirst(strtolower(str_replace('_', ' ', $paymentmethod->type))) : '-' }}

                                </td>

                                <td class="pt-4">
                                    {{ $paymentmethod ? '**** **** **** ' . $paymentmethod->last_four_digits : '-' }}
                                </td>
                                <td class="pt-4">
                                    {{ $paymentmethod->expiry_month ? $paymentmethod->expiry_month . '/' . $paymentmethod->expiry_year : '-' }}
                                </td>
                                <td class="status pt-4"><span class="{{$paymentmethod->status === 'active' ? 'badge-success p-0' : 'badge-fail p-0'}}">{{ $paymentmethod->status ? ucfirst(strtolower(str_replace('_', ' ', $paymentmethod->status))) : '-' }}</span>

                                </td>
                                <td class="pt-4">
                                    @if($subscription)
                                    <div class="col-lg mb-2">
                                        <a href="update-payment-method/{{$subscription->subscription_id}}" class="btn btn-primary btn-sm ">Update</a>
                                    </div>
                                    @endif
                                </td>

                            </tr>

                            @else
                            <tr>
                                <td colspan="5">No payment method available</td>
                            </tr>

                            @endif


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="modal fade" id="addressUpdateModal" tabindex="-1" aria-labelledby="addressUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content bg-popup">
                <div class="modal-header">
                    <h3 class="modal-title " id="addressUpdateModalLabel">Update Address</h3>
                    <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                </div>
                <div class="modal-body">
                    <form action="/update-address" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-lg">
                                <label class="fw-bold">Address*</label>
                                <input name="address" class="form-control">
                            </div>

                        </div>
                        <div class="row popup-element">
                            <div class="col-lg-3">
                                <label class="fw-bold">Zip Code*</label>
                                <input name="zip_code" class="form-control">
                            </div>
                            <div class="col-lg-7">
                                <label class="fw-bold">City*</label>
                                <input name="city" class="form-control">
                            </div>
                            <div class="col-lg-2">
                                <label class="fw-bold">State*</label>
                                <input name="state" class="form-control">
                            </div>
                        </div>
                        <input name="country" value="United States" hidden />
                        <input type="submit" class="btn btn-primary px-3 py-2 rounded popup-element" value="Update Address">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="passwordUpdateModal" tabindex="-1" aria-labelledby="passwordUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered ">
            <div class="modal-content bg-popup">
                <div class="modal-header">
                    <h3 class="modal-title" id="passwordUpdateModalLabel">Change Password</h3>
                    <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                </div>
                <div class="modal-body">
                    <form id="passwordUpdateForm" action="/update-password" method="post">
                        @csrf
                        <label class="fw-bold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" />
                        <label class="fw-bold popup-element">New Password</label>
                        <input type="password" name="new_password" class="form-control " />
                        <label class="fw-bold popup-element">Confirm New Password</label>
                        <input type="password" name="confirm_new_password" class="form-control" />
                        <input type="submit" class="btn btn-primary px-3 py-2 rounded popup-element" value="Update Password">
                    </form>
                    <div class="text-dark popup-element">
                        <h4 class="fw-bold">Password Instructions:</h4>
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
</div>
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-popup">
            <div class=" modal-header">
                <h3 class="modal-title" id="exampleModalLabel">Invite User </h3>
                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body">
                <form action="/invite-user" method="post">
                    @csrf
                    <div>
                        <div class=" mb-3 row">
                            <div class="col-lg">
                                <input name="first_name" class="ms-2 form-control" placeholder="First Name*" required>
                            </div>
                            <div class="col-lg">
                                <input name="last_name" class="ms-2 form-control" placeholder="Last Name*" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-lg">
                            <input name="email" class="ms-2 form-control" placeholder="Email*" required>
                        </div>
                        <div class="col-lg">
                            <input name="phone_number" class="ms-2 form-control" placeholder="Phone Number*" required>
                        </div>
                    </div>
                    <input name="zoho_cust_id" value="{{$partner->zoho_cust_id}}" hidden />
                    <input type="submit" class="btn btn-primary text-white px-3 py-2 rounded " value="Save Changes">
                </form>
            </div>
        </div>
    </div>
</div>
@include('layouts.show-alert-modal')
@endsection