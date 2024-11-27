@extends('layouts.partner_template')

@section('content')

<div class="row">
    <div class="col-md-6">
        <a href="/" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <div class="col-md-6">

    </div>
</div>

<div class="subscribe-card p-4 shadow border-0 border-top border-primary bg-clearlink">
    <div>
        <div class="row">

            <div class="col-md-6 border-0 border-end px-2">
                <div class="">


                    <p><strong>Information:</strong></p>
                    <ul class="billing">

                        <li class="billing mb-1"> The total amount for the Monthly Add-On will be charged once you complete the purchase process.</li>

                    </ul>

                </div>
            </div>
            <div class="col-md-6 col-split ps-5 pe-2">

                <h4 class="mb-3">Current Plan Details</h4>

                <p>{{$subscription->subscription_number}}</p>

                <p>{{$subscription->plan_name}}</p>

                <p>US&nbsp;${{number_format($subscription->price,2)}}</p>

                <p><small>Next Renewal Date {{ Carbon\Carbon::parse($subscription->next_billing_at)->format('d-M-Y') }}</small></p>

                <h4 class="mb-3">Add-On Plan Details</h4>

                <div class="row">
                    <div class="col-md-12">
                        <p class="mb-3"><strong>You are upgrading to</strong></p>
                        <p>{{$addon->name}}</p>
                        <p>US&nbsp;${{number_format($addon->addon_price,2)}}</p>

                    </div>
                    <div class="col-md-12">
                    </div>
                    <div class="col-md-12">
                        <a data-bs-toggle="modal" data-bs-target="#termsModal" class="btn btn-primary">Add Add-On</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content bg-popup">
            <div class="modal-header  bg-popup">
                <h3 class="modal-title fw-bold" id="exampleModalLabel">Terms and Conditions</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-5">
                @include('layouts.terms_conditions')
            </div>
            <div class="modal-footer d-flex flex-row justify-content-end  bg-popup">
                <div>
                    <input class="me-2 fs-5 form-check-input-lg " type="checkbox" id="myCheckbox" />
                    <span><strong>I agree to these Terms and Conditions</strong></span>
                </div>
                <a id="myLink" href="/select-addon/{{$addon->addon_code}}" class="btn btn-primary disabled">Submit</a>
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