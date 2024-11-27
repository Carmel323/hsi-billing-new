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

                            <th class=" align-middle position-relative">
                                @php
                                $plan_words = explode(' ', $plan['plan_name']);
                                $plan_first_name = $plan_words[0];
                                $plan_last_name = implode(' ', array_slice($plan_words, 1));
                                @endphp
                                <div>

                                    <h5 class="text-dark mb-2"><strong><span class="text-primary">{{ $plan_first_name }}</span>&nbsp;<span>{{ $plan_last_name }}</span></strong></h5>
                                    @if($plan_first_name !== "Custom")
                                    <h5 class="text-dark mb-2"><strong>${{ number_format($plan['price'],2) }} </strong></h5>
                                    @endif

                                    <a class="btn btn-primary btn-sm" href="select-plan/{{$plan['plan_id']}}">Select Plan</a>

                                    <a type="button" class="text-decoration-none mt-2 rounded mobile-visible" data-bs-toggle="modal" data-bs-target="#{{ $plan['plan_id'] }}">View Features</a>
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

@endsection