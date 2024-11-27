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
<div class="subscribe-card p-4 shadow border-0 border-top border-primary mb-5 bg-clearlink">
    <div>
        <div class="row">
            <div class="col-md-6 border-0 border-end px-2">
                <div class="">

                    <h6><strong>Information:</strong></h6>
                    <ul class="billing">

                        <li class="billing mb-1">We've implemented calendar billing on the 1st of every month. If you subscribe after the 1st of the month, the first upgrade will be prorated. Subsequent renewals will be charged for the full calendar month.</li>

                    </ul>

                </div>
            </div>
            <div class="col-md-6 col-split ps-5 pe-2">

                <h4 class="mb-3">Current Plan Details</h4>

                <p>{{$subscription->subscription_number}}</p>

                <p>{{$subscription->plan_name}}</p>

                <p>US&nbsp;${{number_format($subscription->price)}}</p>

                <p><small>Next Renewal Date {{ Carbon\Carbon::parse($subscription->next_billing_at)->format('d-M-Y') }}</small></p>

                <h4 class="mb-3">Selected New Plan Details</h4>

                <div class="row">
                    <div class="col-md-12">
                        <p class="mb-3"><strong>You are upgrading to</strong></p>
                        <p>{{$plan->plan_name}}</p>
                        <p>US&nbsp;${{number_format($plan->price)}}</p>

                    </div>
                    <div class="col-md-12">
                    </div>

                    <div class="col-md-12">
                        <a data-bs-toggle="modal" data-bs-target="#termsModal{{$plan->id}}" class="btn btn-primary">Change Plan</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="termsModal{{$plan->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content bg-popup ">
            <div class="modal-header bg-popup">
                <h3 class="modal-title fw-bold" id="exampleModalLabel">Terms and Conditions</h3>
                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body terms-modal">
                @include('layouts.terms_conditions')
            </div>
            <div class="modal-footer d-flex flex-row justify-content-end bg-popup">
                <div>
                    <input class="me-2 fs-5 form-check-input-lg " type="checkbox" id="myCheckbox" />
                    <span><strong>I agree to these Terms and Conditions</strong></span>
                </div>
                <a id="myLink" href="/subscribe-update/{{$plan->plan_id}}" class="btn btn-primary disabled">Submit</a>
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