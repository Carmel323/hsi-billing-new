@extends('layouts.partner_template')
@section('content')

<div class="d-flex flex-column justify-content-center align-items-center ">

    <div class="d-flex flex-row row m-2 mb-0 w-100 justify-content-center align-items-center">


        <div class="tableFixHead p-0 mt-1 price-table">
            <div class="">
                <table class="table table-bordered m-0 w-full text-center pricing-table ">
                    <thead class="border-bottom  shadow">
                        <tr>
                            <th class=" align-middle fixed-column">
                                <h2 class="fw-bold ">Plan Features</h2>
                            </th>
                            @foreach($plans as $plan)
                            <div class="modal fade" id="addonConfirmationModal{{$plan['plan_id']}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content bg-popup">
                                        <div class="modal-header">
                                            <p class="modal-title fw-bold me-3 mt-3" id="exampleModalLabel">If you have any add-ons, you may lose their benefits. Do you still want to upgrade?</p>
                                            <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                                        </div>
                                        <div class="modal-footer">
                                            <a class="btn btn-primary btn-sm popup-element" href="change-plan/{{$plan['plan_id'] }}" id="save">Proceed</a>
                                            <button type=" button" class="btn btn-secondary btn-sm popup-element" data-bs-dismiss="modal">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <th class=" align-middle position-relative">
                                @php
                                $plan_words = explode(' ', $plan['plan_name']);
                                $plan_first_name = $plan_words[0];
                                $plan_last_name = implode(' ', array_slice($plan_words, 1));
                                @endphp
                                <div>
                                    @if(!$plan['is_current_plan'] && $plan['is_enterprise_plan'] && $plan['plan_code'] == 'enterprise')

                                    <h5 class="mb-2"><strong><span class="text-primary">Custom</span><span> Enterprise</span></strong></h5>
                                    @if($availability_data === null || $company_info === null)
                                    <a data-bs-toggle="modal" data-bs-target="#showAlertModal" id="save" class="btn btn-primary">Contact Us</a>
                                    @else
                                    <a data-bs-toggle="modal" data-bs-target="#contactModal" id="save" class="btn btn-primary">Contact Us</a>
                                    @endif

                                    @elseif($plan['is_current_plan'])

                                    <h5 class="text-dark mb-2"><strong><span class="text-primary">{{ $plan_first_name }}</span>&nbsp;<span>{{ $plan_last_name }}</span></strong></h5>
                                    @if($subscription->addon)
                                    @php
                                    $addon = DB::table('add_ons')->where('addon_code',$subscription->addon)->first();
                                    @endphp
                                    <h5 class="text-dark mb-2"><strong>${{ number_format($plan['price']) }} + {{ number_format($addon->addon_price)}}</strong></h5>
                                    @else
                                    <h5 class="text-dark mb-2"><strong>${{ number_format($plan['price']) }} </strong></h5>
                                    @endif
                                    <span class="position-absolute top-0 start-50 rounded-1 border border-2 fs-6 translate-middle badge text-primary bg-white">Current Plan</span>
                                    @if(!empty($plan['addon']))
                                    <p class="mt-1 fw-normal p-1"><small>You have also Subscribed to: <span>{{ ucwords($plan['plan_name'])}}</span> ADD-ON for the current month</small></p>
                                    @elseif(empty($plan['addon'])&& $plan_hasaddon)
                                    @if($number_of_addons > 1)
                                    <button type="button" class="btn btn-primary mt-2 mb-2" data-bs-toggle="modal" data-bs-target="#selectAddon">
                                        Monthly Click Add-On
                                    </button>
                                    @else
                                    <a href="addon-plan/{{$plan['plan_id']}}" id="save" class="btn btn-primary mt-2 mb-2">Monthly Click Add-On</a>
                                    @endif
                                    @endif
                                    <p class="mt-2 mb-2"><small>Next Renewal Date: {{ Carbon\Carbon::parse($plan['next_billing_at'])->format('d-M-Y') }}</small></p>

                                    <a type="button" class="text-decoration-none mt-2 rounded mobile-visible" data-bs-toggle="modal" data-bs-target="#{{ $plan['plan_id'] }}">View Features</a>

                                    @else

                                    <h5 class="text-dark mb-2">
                                        <strong><span class="text-primary">{{ $plan_first_name }}</span>&nbsp;<span>{{ $plan_last_name }}</span></strong>
                                    </h5>

                                    <h5 class="text-dark mb-2">
                                        <strong>${{ number_format($plan['price']) }}</strong>
                                    </h5>


                                    @if($plan['is_upgrade_possible'])
                                    @if($subscription->addon)
                                    <a style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#addonConfirmationModal{{$plan['plan_id']}}" class="btn btn-primary">Upgrade</a>
                                    @else
                                    <a href="change-plan/{{$plan['plan_id'] }}" id="save" class="btn btn-primary">Upgrade </a>
                                    @endif
                                    @endif



                                    @if($plan_subscribed === null )

                                    @if($availability_data === null || $company_info === null)
                                    <a style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#showAlertModal" class="btn btn-primary">Subscribe</a>
                                    @else
                                    <a href="subscribe-plan/{{$plan['plan_id']}}" id="save" class="btn btn-primary text-white">Subscribe</a>
                                    @endif

                                    @elseif($plan_subscribed === null && $subscription->status === 'non_renewing')

                                    <a href="change-plan/{{$plan->plan_id}}" id="save" class="btn btn-primary text-white">Subscribe</a>

                                    @endif


                                    <a type="button" class="text-decoration-none mt-2 rounded mobile-visible" data-bs-toggle="modal" data-bs-target="#{{ $plan['plan_id'] }}">View Features</a>
                                    @endif
                                </div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fixed-column align-left fs-5 fw-bold">
                                <div>
                                    <ul>
                                        <li>Update Logo</li>
                                        <li>Custom URL</li>
                                        <li>Zip code availability updates</li>
                                        <li>Data updates (speeds, connection types)</li>
                                        <li>Self service portal access</li>
                                        <li>Account management support</li>
                                        <li>Reporting</li>
                                        <li>Maximum Allowed Clicks</li>
                                        <li>Maximum Click Monthly Add-On</li>
                                    </ul>
                                </div>
                            </td>
                            @foreach($plans as $plan)
                            <td>

                                <div>
                                    <ul>
                                        <li><i class="{{ $plan['features']['update_logo'] ? 'fa-solid fa-check text-check fs-3' : 'fa-solid fa-xmark text-cross fs-3' }}"></i></li>
                                        <li><i class="{{ $plan['features']['custom_url'] ? 'fa-solid fa-check text-check fs-3' : 'fa-solid fa-xmark text-cross fs-3' }}"></i></li>
                                        <li> <i class="{{ $plan['features']['zip_code_availability_updates'] ? 'fa-solid fa-check text-check fs-3' : 'fa-solid fa-xmark text-cross fs-3' }}"></i></li>
                                        <li> <i class="{{ $plan['features']['data_updates'] ? 'fa-solid fa-check text-check fs-3' : 'fa-solid fa-xmark text-cross fs-3' }}"></i></li>
                                        <li><i class="{{ $plan['features']['self_service_portal_access'] ? 'fa-solid fa-check text-check fs-3' : 'fa-solid fa-xmark text-cross fs-3' }}"></i></li>
                                        <li> <i class="{{ $plan['features']['account_management_support'] ? 'fa-solid fa-check text-check fs-3' : 'fa-solid fa-xmark text-cross fs-3' }}"></i></li>
                                        <li>{{ $plan['features']['reporting']}} </li>
                                        <li>{{ $plan['features']['maximum_allowed_clicks'] }}</i></li>
                                        <li> {{ $plan['features']['maximum_click_monthly_add_on'] }}</i></li>
                                    </ul>
                                </div>
                            </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>



<div class="modal fade" id="selectAddon" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-popup">
            <div class="modal-header">
                <h3 class="modal-title fs-5" id="exampleModalLabel">Select the Add-On to add</h3>
                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body">
                <form action="/get-addon" method="get">
                    @csrf
                    <select type="text" name="addon_code" class="form-select" required>
                        @if($addons)
                        @foreach($addons as $addon)
                        <option value="{{$addon->addon_code}}">{{$addon->name}}</option>
                        @endforeach
                        @endif
                    </select>
            </div>
            <div class="modal-footer">
                <input type="submit" class="btn btn-primary" value="Add Add-On" />
                </form>
            </div>
        </div>
    </div>
</div>


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
                    <textarea class="w-100 p-3 pe-4 border-0 rounded" name=" message">I am interested in learning more about the Enterprise plan. Please contact me with more information.</textarea>
                    <input type="submit" class="btn btn-primary popup-element " value="Send">
                </form>
            </div>
            <div class="modal-footer border-0"></div>
        </div>
    </div>
</div>

<!-- Individual Plan Modals -->
@foreach ($plans as $plan)
<div class="modal fade" id="{{ $plan['plan_id'] }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-popup">
            <div class="modal-header">
                <h3 class="modal-title text-primary" id="staticBackdropLabel">{{ $plan['plan_name'] }} Features</h3>
                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body">

                <table class="text-center mt-3 table table-bordered feature-table">

                    <tbody>
                        @foreach ($plan['features'] as $featureKey => $featureValue)
                        <tr>
                            <td>{{ ucwords(str_replace('_', ' ', $featureKey)) }}</td>
                            <td>
                                @if ($featureValue === true)
                                <i class="fa-solid fa-check text-check fs-3"></i>
                                @elseif ($featureValue === false)
                                <i class="fa-solid fa-xmark text-cross fs-3"></i>
                                @else
                                {{ $featureValue }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endforeach

@include('layouts.quick_info')

@include('layouts.show-alert-modal')
@endsection