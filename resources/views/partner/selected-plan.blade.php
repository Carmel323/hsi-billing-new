@extends('layouts.partner_template')

@section('content')

<div class="row">
    <div class="col-md-6">
        <a href="/" class="btn text-primary text-decoration-underline mb-3">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <div class="col-md-6">

    </div>
</div>
@php
use Carbon\Carbon;
$today = Carbon::today();
$endOfMonth = $today->copy()->endOfMonth();
$remainingDays = $today->diffInDays($endOfMonth) + 1;
@endphp
@if($plan === "custom_enterprise")
<div class="subscribe-card p-4 shadow border-0 border-top border-primary mb-5 bg-clearlink">
    <div>
        <div class="subscribe-row row m-5 ">
            <div class="col-md-6 subscribe-column border-0 border-end px-2">
                <div>
                    <p class="lh-lg pe-5"><strong>Are you interested in learning more about the Enterprise plan? You will be contacted for more information!.</strong></p>

                </div>
            </div>
            <div class="col-md-6 col-split ps-5 pe-2 align-items-center d-flex ">


                <div class="row">
                    <h4 class="mb-3">Plan Details</h4>
                    <div class="col-md-12">
                        <p class="mb-3"><strong>You are selecting </strong></p>
                        <p>Custom Enterprise</p>
                    </div>
                    <div class="col-md-12">
                    </div>
                    <div class="col-md-12">
                        <a data-bs-toggle="modal" data-bs-target="#termsModal" class="btn btn-primary">Add Payment Method</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="subscribe-card p-4 shadow border-0 border-top border-primary mb-5 bg-clearlink">
    <div>
        <div class="subscribe-row row m-5 ">
            <div class="col-md-6 subscribe-column border-0 border-end px-2">
                <div>
                    <p><strong>Information:</strong></p>
                    <ul class="billing">

                        <li class="billing mb-1">We've implemented calendar billing on the 1st of every month. If you subscribe after the 1st of the month, the first month will be prorated. Subsequent renewals will be charged for the full calendar month.</li>
                    </ul>

                </div>
            </div>
            <div class="col-md-6 col-split ps-5 pe-2 align-items-center d-flex ">


                <div class="row">
                    <h4 class="mb-3">Plan Details</h4>
                    <div class="col-md-12">
                        <p class="mb-3"><strong>You are selecting </strong></p>
                        <p>{{$plan->plan_name}}</p>
                        <p>US&nbsp;${{number_format($plan->price,2)}}</p>
                    </div>
                    <div class="col-md-12">
                    </div>
                    <div class="col-md-12">
                        <a data-bs-toggle="modal" data-bs-target="#termsModal" class="btn btn-primary">Add Payment Method</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif



<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content bg-popup ">
            <div class="modal-header bg-popup">
                <h3 class="modal-title fw-bold" id="exampleModalLabel">Terms and Conditions</h3>
                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body terms-modal bg-popup">
                @include('layouts.terms_conditions')
            </div>
            <div class="modal-footer d-flex flex-row justify-content-end bg-popup">
                <div>
                    <input class="me-2 fs-5 form-check-input-lg " type="checkbox" id="myCheckbox" />
                    <span><strong>I agree to these Terms and Conditions</strong></span>
                </div>
                <a id="myLink" href="/add-payment-method/{{$partner_id}}" class="btn btn-primary disabled">Submit</a>

            </div>
        </div>
    </div>
</div>
<script>
    const checkbox = document.getElementById("myCheckbox");
    const link = document.getElementById("myLink");

    checkbox.addEventListener("change", function() {
        if (checkbox.checked) {
            link.classList.remove("disabled");
        } else {
            link.classList.add("disabled");
        }
    });
</script>
@endsection