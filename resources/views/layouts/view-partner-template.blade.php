@extends('layouts.admin_template')

@section('content')
<div class="d-flex flex-row ">
    <h5 class="fw-bold mt-1">{{$partner->company_name}}</h5>
    @if($partner->status ==='active')
    <span class="badge-warning p-1 status ms-3 mb-2">Setup In Progress</span>
    @elseif($partner->status ==='inactive')
    <span class="badge-fail p-1 status ms-3 mb-2">{{ $partner->status }}</span>
    @elseif($partner->status === 'Invited')
    <span class="badge-revoked p-1 status ms-3 mb-2">{{ $partner->status }}</span>
    @elseif($partner->status === 'completed')
    <span class="badge-success p-1 status ms-3 mb-2">Setup Completed</span>
    @endif
    @if($partner->status !== 'completed')
    <a class="btn btn-primary btn-sm mb-2 ms-3" data-bs-toggle="modal" data-bs-target="#showAlertModal">Mark Setup As Completed</a>
    @endif
</div>

<nav class="navbar1 navbar-expand-lg border-bottom border-dark">
    <div class="container-fluid">

        <ul class="navbar-nav">
            <li class="nav-item m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id) ? 'nav-link nav-active' : 'nav-link' }}" aria-current="page" href="/admin/view-partner/{{$partner->id}}">Overview</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/subscriptions') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/subscriptions">Subscriptions</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/invoices') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/invoices">Invoices</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/creditnotes') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/creditnotes">Credit Notes</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/refunds') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/refunds">Refunds</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/provider-data') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/provider-data">Provider Data</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/clicks-data') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/clicks-data">Clicks Data</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/selected-plans') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/selected-plans">Select Plans</a>
            </li>
        </ul>

    </div>
</nav>
<div class="modal fade" id="showAlertModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-popup">
            <div class="modal-header d-flex justify-content-between border-0 bg-popup">
                <h5 class="fw-bold message"> Please complete the following to create a Subscription </h5>
                <button type="button" class="close border-0 bg-popup" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid text-dark fa-xmark fs-3 mb-4"></i></button>
            </div>
            <div class=" modal-body ">

                <ul class=" message">
                    <li class="d-flex justify-content-between"><span>Upload Logo (Company Info)</span>@if($company_info) <i class="fa-solid fa-check text-check fs-3"></i>@endif</li>
                    <li class="d-flex justify-content-between"><span>Add Company Name (Company Info) </span>@if($company_info) <i class="fa-solid fa-check text-check fs-3"></i>@endif</li>
                    <li class="d-flex justify-content-between"><span>Set Landing Page URL (Company Info)</span>@if($company_info) <i class="fa-solid fa-check text-check fs-3"></i>@endif</li>
                    <li class="d-flex justify-content-between"><span>Upload Provider Data</span>@if($availability_data) <i class="fa-solid fa-check text-check fs-3"></i>@endif</li>

                </ul>

            </div>
            <form action="/charge-subscription" method="post">
                @csrf
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label for="advertiser_id" class="form-label fw-bold">Advertiser ID*</label>
                        <input name="advertiser_id" value="{{$partner->isp_advertiser_id}}" class=" form-control" placeholder="Advertiser ID*" required>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label for="tune_link" class="form-label fw-bold">Tune Link*</label>

                        <input name="tune_link" value="{{isset($company_info->tune_link)?$company_info->tune_link:''}}" class=" form-control" placeholder="Tune Link*" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label for="plan_code" class="fw-bold form-label mt-3 me-2">Select Plan</label>
                        <select name="plan_code" class="form-control" required>
                            <option value="">Select Plan</option>
                            @if($selected_plan)
                            <option value="{{ $selected_plan->plan_code }}" selected>{{$selected_plan->plan_name}} - ${{ $selected_plan->price }}</option>
                            @endif
                            @foreach($plans as $plan)
                            <option value="{{ $plan->plan_code }}">{{ $plan->plan_name }} - ${{ $plan->price }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label for="budget_cap" class="form-label  mt-3 fw-bold"> Budget Cap*</label>
                        @if($budget_cap)
                        @if($budget_cap->plan_type === 'flat')
                        <input name="budget_cap" value="{{isset($budget_cap->click_limit)?$budget_cap->click_limit:''}}" class=" form-control" placeholder=" Budget Cap*" required>
                        @else
                        <input name="budget_cap" value="{{isset($budget_cap->cost_limit)?$budget_cap->cost_limit:''}}" class=" form-control" placeholder=" Budget Cap*" required>
                        @endif
                        @else
                        <input name="budget_cap" class=" form-control" placeholder=" Budget Cap*" required>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="d-flex flex-row  justify-content-between">
                        <div>
                            @if($paymentmethod)
                            @if($paymentmethod->type === "bank_account")
                            <label class="fw-bold">Bank Details</label>
                            @elseif($paymentmethod->type === "card")
                            <label class="fw-bold">Card Details</label>
                            @endif
                            @else
                            <h4>Payment Details</h4>
                            @endif

                        </div>
                        <div>
                            @if($paymentmethod === null)
                            <a href="add-payment-method/{{$partner->id}}"><i data-toggle="tooltip" title="Associate a payment method" class="fa-solid fa-circle-plus"></i></a>
                            @endif
                        </div>

                    </div>
                    <div class="col-lg">
                        <div class="card w-100 border-1 rounded my-1 bg-white">
                            <div class="card-body d-flex flex-row justify-content-between">
                                <div class="text-dark fw-bold">
                                    @if($paymentmethod)
                                    @if($paymentmethod->type === "bank_account")
                                    <i class="fa-solid fa-building-columns text-primary me-3"></i>
                                    @elseif($paymentmethod->type === "card")
                                    <i class="fa-regular fa-credit-card text-primary me-3"></i>
                                    @endif
                                    {{ $paymentmethod ? 'XXXX XXXX XXXX ' . $paymentmethod->last_four_digits : '-' }}
                                    @else
                                    <span class="fw-normal"> No details found</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="text" value="{{$partner->zoho_cust_id}}" name="partner_id" class="form-control" hidden>
                <button type="submit" class="btn btn-primary mt-3 mb-2">Create Subscription</button>
            </form>
            <div class="modal-footer border-0">

            </div>
        </div>
    </div>
</div>
<div>
    @yield('child-content')
</div>

@endsection