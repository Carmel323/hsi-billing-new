@extends('layouts.view-partner-template')

@section('child-content')

<div style="width:80%" class="d-flex flex-row justify-content-between mt-5">
    <div>
        <h5 class="fw-bold">Subscriptions</h5>
    </div>
    <div>
        @if($subscriptions->isNotEmpty())
        <a data-bs-toggle="modal" data-bs-target="#updateSubscriptionModal" style="cursor:pointer;" class="text-dark fw-bold"><i class="fa-solid fa-circle-plus me-2 text-primary"></i>Update Subscription</a>
        @else
        @if($availability_data === null || $company_info === null)
        <a style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#showAlertModal" class="text-dark fw-bold"><i class="fa-solid fa-circle-plus me-2 text-primary"></i>Create Subscription</a>
        @else
        <a data-bs-toggle="modal" data-bs-target="#createSubscriptionModal" style="cursor:pointer;" class="text-dark fw-bold"><i class="fa-solid fa-circle-plus me-2 text-primary"></i>Create Subscription</a>
        @endif
        @endif
    </div>
</div>

<div class="card border partner-card border-dark ms-0 m-2 mb-5">
    <div class="card-body table-responsive p-0">

        @if($subscriptions->isNotEmpty())
        <table class="text-center mt-1 m-0 table border-dark   rounded">
            <thead class="">
                <tr>
                    <th class="fw-normal">Creation Date</th>
                    <th class="fw-normal">Subscription Number</th>
                    <th class="fw-normal">Plan Name</th>
                    <th class="fw-normal">Amount(USD)</th>
                    <th class="fw-normal">Next Billing Date</th>
                    <th class="fw-normal">Status</th>

                </tr>
            </thead>

            <tbody id="invoiceTable">
                @foreach($subscriptions as $index => $subscription)
                <tr class="py-3 text-center ">
                    <td data-label="start_date" class="fw-bold">{{ Carbon\Carbon::parse($subscription->start_date)->format('d-M-Y') }}</td>
                    <td data-label="Subscription Number" class="text-primary">{{$subscription->subscription_number}}</td>
                    <td data-label="Plan Name" class="fw-bold">{{$subscription->plan_name}}</td>
                    <td data-label="Amount" class="fw-bold">{{$subscription->price}}</td>

                    <td data-label="Next Billing" class="fw-bold">{{ Carbon\Carbon::parse($subscription->next_billing_at)->format('d-M-Y') }}</td>
                    <td data-label="Status" class="{{$subscription->status === 'live'? 'text-success':'text-danger'}}">{{$subscription->status}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <!-- <div class="d-flex justify-content-between m-0 p-0 align-items-center">
            <div class="d-flex align-items-center ">
                <p class="fw-bold m-0 p-4">Total Count</p>
            </div>
            <div>

            </div>
        </div> -->
        <div class="modal fade" id="updateSubscriptionModal" tabindex="-1" aria-labelledby="updateSubscriptionModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-popup">
                    <div class="modal-header">
                        <h3 class="modal-title">Enter the required details</h3>
                        <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                    </div>
                    <div class="modal-body">
                        <form action="/update-subscription" method="post">
                            @csrf

                            <input type="text" value="{{$partner->zoho_cust_id}}" name="partner_id" class="form-control" hidden>
                            <label for="plan_code" class="fw-bold form-label mt-3">Plans *</label>
                            <select name="plan_code" class="form-control" required>
                                <option value="">Select Plan</option>
                                @foreach($plans_for_update as $plan)
                                <option value="{{ $plan->plan_code }}">{{ $plan->plan_name }} - ${{ $plan->price }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary mt-3 mb-2">Save</button>
                        </form>
                    </div>
                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="modal fade" id="createSubscriptionModal" tabindex="-1" aria-labelledby="createSubscriptionModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-popup">
                    <div class="modal-header">
                        <h3 class="modal-title">Enter the required details</h3>
                        <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                    </div>
                    <div class="modal-body">
                        <form action="/create-subscription" method="post">
                            @csrf
                            <input type="text" value="{{$partner->zoho_cust_id}}" name="partner_id" class="form-control" hidden>
                            <label for="plan_code" class="fw-bold form-label mt-3">Plans *</label>
                            <select name="plan_code" class="form-control" required>
                                <option value="">Select Plan</option>
                                @foreach($plans as $plan)
                                <option value="{{ $plan->plan_code }}">{{ $plan->plan_name }} - ${{ $plan->price }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary mt-3 mb-2">Save</button>
                        </form>
                    </div>
                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center align-items-center m-5  ">
            <p class="m-0 p-0">No Subscriptions made</p>
        </div>
        @endif
    </div>
</div>
<div class="modal fade" id="showAlertModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-popup">
            <div class="modal-header d-flex justify-content-end border-0 bg-popup">
                <button type="button" class="close border-0 bg-popup" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid text-dark fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body mb-5">
                <h3 class="message"> Please complete the following to create a Subscription </h3>
                <ul class="message">
                    <li class="d-flex justify-content-between"><span>Upload Logo (Company Info)</span>@if($company_info) <i class="fa-solid fa-check text-check fs-3"></i>@endif</li>
                    <li class="d-flex justify-content-between"><span>Add Company Name (Company Info) </span>@if($company_info) <i class="fa-solid fa-check text-check fs-3"></i>@endif</li>
                    <li class="d-flex justify-content-between"><span>Set Landing Page URL (Company Info)</span>@if($company_info) <i class="fa-solid fa-check text-check fs-3"></i>@endif</li>
                    <li class="d-flex justify-content-between"><span>Upload Provider Data</span>@if($availability_data) <i class="fa-solid fa-check text-check fs-3"></i>@endif</li>
                </ul>
            </div>
            <div class="modal-footer border-0">
            </div>
        </div>
    </div>
</div>
@endsection