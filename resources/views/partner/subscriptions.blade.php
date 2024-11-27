@extends('layouts.partner_template')

@section('content')

@if($subscriptions->isNotEmpty())
@foreach($subscriptions as $subscription)
<div class="d-flex justify-content-center align-items-center">
  <div style="width:100%;" class="row mb-0 border  shadow">

    <div class="col-lg border-0 bg-clearlink">
      <div class="d-flex flex-column  ">
        <div id="carouselExampleControls" class="carousel slide carousel-fade" data-bs-ride="carousel">
          <!-- Modal -->
          <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content bg-popup">
                <div class="modal-header">
                  <h3 class="modal-title " id="exampleModalLabel">Select a plan to downgrade</h3>
                  <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                </div>
                <div class="modal-body">
                  <form action="/email" method="post">
                    @csrf
                    <div class="d-flex flex-column">
                      <select id='plan_name' name='plan_name' class="mt-4 form-select-lg border-dark shadow-none" required="" style="width:300px; ">
                        <option class="py-3" value="">Select a Plan</option>
                        @foreach($plans_ascs as $plans_asc)
                        <option class="py-3" value="{{$plans_asc->plan_name}}">{{$plans_asc->plan_name}}</option>
                        @endforeach
                      </select>
                      <input type="submit" class="mt-5 w-25 btn btn-primary rounded" value="Submit">
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <div class="modal fade" id="upgradeModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content bg-popup">
                <div class="modal-header">
                  <h3 class="modal-title" id="exampleModalLabel">Select a plan to upgrade</h3>
                  <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                </div>
                <div class="modal-body">
                  <form action="/upgrade" method="post">
                    @csrf
                    <div class="d-flex flex-column">
                      <select id='plan_name' name='plan_id' class="mt-4 form-select-lg border-dark shadow-none" required="" style="width:300px; ">
                        <option class="py-3" value="">Select a Plan</option>
                        @foreach($plans_orders as $plans_order)
                        <option class="py-3" value="{{$plans_order->plan_id}}">{{$plans_order->plan_name}}</option>
                        @endforeach
                      </select>
                      <input type="submit" class="mt-5 w-25 btn btn-primary rounded" value="Submit">
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal -->
          <div class="modal fade" id="addonConfirmationModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content bg-popup">
                <div class="modal-header">
                  <h6 class="modal-title fw-bold me-4 mt-3" id="exampleModalLabel">If you have any add-ons, you may lose their benefits. Do you still want to upgrade?</h6>
                  <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary btn-sm popup-element" data-bs-toggle="modal" data-bs-target="#upgradeModal">Proceed</button>
                  <button type=" button" class="btn btn-secondary btn-sm popup-element" data-bs-dismiss="modal">Cancel</button>
                </div>
              </div>
            </div>
          </div>


          <div class=" text-start w-full p-3 border-0 bg-clearlink">
            <h3 class="text-primary fw-bold mb-3 m-0 text-uppercase p-0"><strong>{{$subscription->plan_name}}</strong></h3>
            <h4 class="">{{$subscription->subscription_number}}</h4>
            @if($subscription->addon)
            @php
            $addon = DB::table('add_ons')->where('addon_code',$subscription->addon)->first();
            @endphp
            <div class="d-flex flex-row justify-content-start align-items-start">
              <p>US&nbsp;$</p>
              <h4><strong>{{number_format($subscription->price)}} + {{number_format($addon->addon_price)}}</strong></h4>
            </div>
            @else
            <div class="d-flex flex-row justify-content-start align-items-start">
              <p>US&nbsp;$</p>
              <h4><strong>{{number_format($subscription->price)}}</strong></h4>
            </div>
            @endif


            @if($subscription->status === 'live')
            <span class=" sub-status position-absolute top-0 end-0 p-1 px-3 badge-success mt-3 fs-5">
              <strong>{{$subscription->status}}</strong>
            </span>
            @elseif($subscription->status === 'cancelled')
            <span class="sub-status p-1 px-1 w-25  mt-3 badge-fail fs-5">
              <strong> Cancelled</strong>
            </span>
            @endif



            <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content terms-title bg-popup">
                  <div class="modal-header border-0">
                    <h3 class="modal-title " id="contactModalLabel">Contact Us</h3>
                    <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                  </div>
                  <div class="modal-body p-0">
                    <form action="/enterprise-support" method="post">
                      @csrf
                      <textarea class="w-100 p-3 pe-4" name=" message">I am interested in learning more about the Enterprise plan. Please contact me with more information.</textarea>
                      <input type="submit" class="btn btn-primary popup-element " value="Send">
                    </form>
                  </div>
                  <div class="modal-footer border-0"></div>
                </div>
              </div>
            </div>


            <div class="d-flex flex-row">
              @if($subscription->status === 'live')
              @if($current_plan_has_addon && empty($subscriptionlive->addon))
              <a style="cursor: pointer;" href="/addon-plan/{{$subscription->plan_id}}" class="btn btn-primary my-3 me-5 justify-content-center d-flex align-items-center rounded w-50">Monthly Click Add-On</a>

              @elseif($current_plan_has_addon && $subscription->addon )
              <p class="mt-3 w-50 text-dark">You have also Subscribed to: <span>{{ucwords($subscription->plan_name)}}</span> ADD-ON for the current month</p>

              @endif
              @if($highest_plan->plan_id === $current_plan->plan_id)
              <a id="upgrade-button" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#contactModal" class="btn btn-primary m-3 d-flex align-items-center w-50 justify-content-center rounded  p-2">Contact Us</a>
              @else
              @if($subscription->addon)
              @if(!$subscription->isCustom && $plans_orders->isNotEmpty())
              <a id="upgrade-button" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#addonConfirmationModal" class="btn btn-primary m-3 d-flex align-items-center  w-50 justify-content-center  rounded  p-2">Upgrade</a>
              @endif
              @else
              @if(!$subscription->isCustom && $plans_orders)
              <a id="upgrade-button" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#upgradeModal" class="btn btn-primary m-3 d-flex align-items-center w-50 justify-content-center rounded  p-2">Upgrade</a>
              @endif
              @endif
              @endif
              @endif
            </div>


            <div class="d-flex flex-row">
              @if($subscription->status === 'live')
              <a style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#cancelSubscription" class="text-decoration-underline text-primary my-3 me-5 w-50 ">Cancellation</a>
              @endif
              @if($lowest_plan->plan_id !== $current_plan->plan_id)
              @if($plans_ascs )
              <a style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#exampleModal" class="text-decoration-underline text-primary my-3 me-5 ms-5 w-50 ">Downgrade</a>
              @endif
              @endif
            </div>
            @php
            $is_cpc_plan = stripos($current_plan->plan_code, 'cpc') !== false;
            @endphp
            @if($is_cpc_plan)
            <form action="/metered-billing" method="post">
              @csrf
              <input name="subscription_id" value="{{$subscription->subscription_id}}" hidden />
              @if($subscription->is_metered_billing)
              <input name="metered_billing" value="0" hidden />
              <input type="submit" class="btn btn-danger" value="Disable Metered Billing">
              @else
              <input name="metered_billing" value="1" hidden />
              <input type="submit" class="btn btn-success" value="Enable Metered Billing">
              @endif
            </form>
            @endif


            @if($subscription->status === 'live')
            <p class="mt-2 fw-normal">Next Renewal Date: {{ Carbon\Carbon::parse($subscription->next_billing_at)->format('d-M-Y') }}</p>
            @endif



          </div>
        </div>

      </div>
    </div>

    @if($paymentmethod)
    <div class="col-lg d-flex justify-content-center flex-column shadow align-items-center bg-clearlink">

      @if($paymentmethod->type === "card")
      <div class="credit-card acct">
        <div class="card-details">

          <div class="card-number">
            XXXX XXXX XXXX {{ $paymentmethod->last_four_digits }}
          </div>

          <div class="card-expiry">
            <div class="card-name text-uppercase">{{ $paymentmethod->type }}</div>
          </div>
        </div>
        <div class="expiry">
          <p class="ms-2 m-0 d-inline p-0">
            @if(!empty($paymentmethod->expiry_month))
            <small> VALID TILL {{ $paymentmethod->expiry_month }}/{{ $paymentmethod->expiry_year }}
            </small>
            @else

            @endif
          </p>
        </div>

      </div>
      <a href="update-payment-method/{{$subscription->subscription_id}}" class="text-decoration-underline text-primary mt-2 ">Update Payment Method</a>
      @else
      <p>ACH Bank Account Information</p>
      <div class="border border-3 d-flex flex-row p-3 rounded">
        <i class="fa-solid fa-building-columns me-3"></i>
        <div class="fw-bold">XXXX XXXX XXXX {{$paymentmethod->last_four_digits}}</div>
      </div>
      <a href="update-payment-method/{{$subscription->subscription_id}}" class="text-decoration-underline text-primary mt-2 ">Update Bank Details</a>
      @endif

    </div>
    @endif

    <!-- Button trigger modal -->


    <!-- Modal -->
    <div class="modal fade" id="cancelSubscription" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-popup">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Do you really want to cancel the subscription?</h1>
            <button type="button" class="close border-0 mb-4" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
          </div>
          <div class="modal-footer">
            <a href="/cancel-email" type="button" class="btn btn-primary">Proceed</a>
            <button type="button" data-bs-dismiss="modal" class="btn button-clearlink text-primary fw-bold">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endforeach
@else
<div class="col-lg d-flex justify-content-center h-50 align-items-center">
  <h3>No Subscriptions found.</h3>
</div>
@endif

@include('layouts.show-alert-modal')

@include('layouts.quick_info')

@endsection