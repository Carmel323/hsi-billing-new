@extends('layouts.admin_template')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <a href="/admin" class="btn text-primary text-decoration-underline fw-bold mb-3 p-0 pt-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="col-md-6">

        </div>
    </div>
    <div class="row">
        <!--         <div class="col-lg-4 mb-2">
            <h3 class="fw-bold">Plan Price</h3>
            <form class="mt-3" method="POST" action="/update-plan-price">
                @csrf
                <div class="row">
                    <div class="col-lg-4 mb-2">
                        <input name="plan_price" class="form-control" value="{{$plan->price}}" />
                    </div>
                    <input name="plan_id" value="{{$plan->plan_id}}" hidden />
                    <div class="col-lg">
                        <button type="submit" class="btn button-clearlink text-primary fw-bold">Update Plan Price</button>
                    </div>

                </div>
            </form>
        </div> -->
        <!-- <div class="col-lg-8">
            <div class="d-flex flex-row">
                <h3 class="fw-bold">Associated Add-Ons</h3>
                <a data-bs-toggle="modal" data-bs-target="#add-addon" class="btn btn-primary mb-1 ms-5">Add Add-On</a>
            </div>
            <table class="table table-bordered table-sm text-center">
                <thead class="bg-clearlink">
                    <th>Add-On Name</th>
                    <th>Add-On Price</th>
                    <th></th>
                </thead>
                <tbody>
                    @foreach($addons as $addon)
                    <tr>
                        <form method="POST" action="/update-addon-price">
                            @csrf
                            <td>{{$addon->name}}</td>
                            <td><input name="addon_price" class="form-control text-center" value="{{$addon->addon_price}}" /></td>
                            <input name="addon_code" value="{{$addon->addon_code}}" hidden />
                            <td><button type="submit" class="btn button-clearlink text-primary fw-bold ">Update Add-On Price</button></td>
                        </form>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div> -->
        <div class="d-flex flex-row justify-content-between">
            <div>
                <h3 class="mt-2 mb-4 ">{{$plan->plan_name}}</h3>
            </div>
        </div>
    </div>
    <div class="modal fade" id="add-addon" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-popup">
                <div class="modal-header">
                    <h3 class="modal-title" id="staticBackdropLabel"><strong> Kindly fill the required details</strong></h3>
                    <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                </div>
                <div class="modal-body">
                    <form action="/add-addon" method="post">
                        @csrf
                        <div class="mb-3 row">
                            <div class="col-lg-12">
                                <label for="name" class="form-label fw-bold">Add-On Name*</label>
                                <input type="text" name="addon_name" id="addon_name" class="form-control" value="" required>
                            </div>
                            <div class="col-lg-12 mt-2">
                                <label for="addon_code" class="form-label fw-bold">Add-On Code*</label>
                                <input type="text" name="addon_code" class="form-control" required readonly>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-lg">
                                <label for="recurring_price" class="form-label fw-bold">Add-On Price in USD*</label>
                                <input type="text" name="addon_price" class="form-control" required>
                            </div>
                            <input name="plan_code" value="{{ $plan->plan_code }}" hidden />
                        </div>
                        <button type="submit" class="btn btn-primary px-3 py-2 rounded">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="card mb-5" style="font-size: 14px;">
        <div class="card-body bg-clearlink">
            <div class="row">

                <div class="col-md-6">
                    <h3 class="mt-3 mb-3 fw-bold">Existing Plan Features</h3>
                    <div id="existing-features">
                        @if(!empty($existingFeatures))
                        <p><strong>Plan Code:</strong> {{ $planCode }}</p>
                        <p><strong>Update Logo:</strong> {{ isset($existingFeatures['update_logo']) && $existingFeatures['update_logo'] ? 'Yes' : 'No' }}</p>
                        <p><strong>Custom URL:</strong> {{ isset($existingFeatures['custom_url']) && $existingFeatures['custom_url'] ? 'Yes' : 'No' }}</p>
                        <p><strong>Zip Code Availability Updates:</strong> {{ isset($existingFeatures['zip_code_availability_updates']) && $existingFeatures['zip_code_availability_updates'] ? 'Yes' : 'No' }}</p>
                        <p><strong>Data Updates:</strong> {{ isset($existingFeatures['data_updates']) && $existingFeatures['data_updates'] ? 'Yes' : 'No' }}</p>
                        <p><strong>Self Service Portal Access:</strong> {{ isset($existingFeatures['self_service_portal_access']) && $existingFeatures['self_service_portal_access'] ? 'Yes' : 'No' }}</p>
                        <p><strong>Account Management Support:</strong> {{ isset($existingFeatures['account_management_support']) && $existingFeatures['account_management_support'] ? 'Yes' : 'No' }}</p>
                        <p><strong>Reporting:</strong> {{ isset($existingFeatures['reporting']) ? $existingFeatures['reporting'] : '' }}</p>
                        <p><strong>Maximum Allowed Clicks:</strong> {{ isset($existingFeatures['maximum_allowed_clicks']) ? $existingFeatures['maximum_allowed_clicks'] : '' }}</p>
                        <p><strong>Maximum Click Monthly Add-on:</strong> {{ isset($existingFeatures['maximum_click_monthly_add_on']) ? $existingFeatures['maximum_click_monthly_add_on'] : '' }}</p>
                        @else
                        <p>No existing plan features found.</p>
                        @endif
                    </div>
                </div>

                <!-- Form to Update Plan Features -->
                <div class="col-md-6">
                    <h3 class="mt-3 mb-3 fw-bold">Update Plan Features</h3>
                    <form method="POST" action="{{ route('admin.planfeatures.update', [], false) }}">
                        @csrf

                        <input type="hidden" name="plan_code" value="{{ $planCode }}">

                        <div class="form-group mb-2">
                            <label for="update_logo" class="checkbox-inline">
                                <input type="checkbox" id="update_logo" name="update_logo" class="form-check-input" {{ isset($existingFeatures['update_logo']) && $existingFeatures['update_logo'] ? 'checked' : '' }}> Update Logo
                            </label>
                        </div>
                        <div class="form-group mb-2">
                            <label for="custom_url" class="checkbox-inline">
                                <input type="checkbox" id="custom_url" name="custom_url" class="form-check-input" {{ isset($existingFeatures['custom_url']) && $existingFeatures['custom_url'] ? 'checked' : '' }}> Custom URL
                            </label>
                        </div>
                        <div class="form-group mb-2">
                            <label for="zip_code_availability_updates" class="checkbox-inline">
                                <input type="checkbox" id="zip_code_availability_updates" name="zip_code_availability_updates" class="form-check-input" {{ isset($existingFeatures['zip_code_availability_updates']) && $existingFeatures['zip_code_availability_updates'] ? 'checked' : '' }}> Zip Code Availability Updates
                            </label>
                        </div>
                        <div class="form-group mb-2">
                            <label for="data_updates" class="checkbox-inline">
                                <input type="checkbox" id="data_updates" name="data_updates" class="form-check-input" {{ isset($existingFeatures['data_updates']) && $existingFeatures['data_updates'] ? 'checked' : '' }}> Data Updates
                            </label>
                        </div>
                        <div class="form-group mb-2">
                            <label for="self_service_portal_access" class="checkbox-inline">
                                <input type="checkbox" id="self_service_portal_access" name="self_service_portal_access" class="form-check-input" {{ isset($existingFeatures['self_service_portal_access']) && $existingFeatures['self_service_portal_access'] ? 'checked' : '' }}> Self Service Portal Access
                            </label>
                        </div>
                        <div class="form-group mb-2">
                            <label for="account_management_support" class="checkbox-inline">
                                <input type="checkbox" id="account_management_support" name="account_management_support" class="form-check-input" {{ isset($existingFeatures['account_management_support']) && $existingFeatures['account_management_support'] ? 'checked' : '' }}> Account Management Support
                            </label>
                        </div>
                        <div class="form-group mb-2">
                            <div class="row">
                                <div class="col-lg-4"><label for="reporting">Reporting:</label></div>
                                <div class="col-lg-6"><select class="form-control" id="reporting" name="reporting">
                                        <option value="Monthly" {{ isset($existingFeatures['reporting']) && $existingFeatures['reporting'] == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="Weekly" {{ isset($existingFeatures['reporting']) && $existingFeatures['reporting'] == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="Daily" {{ isset($existingFeatures['reporting']) && $existingFeatures['reporting'] == 'Daily' ? 'selected' : '' }}>Daily</option>
                                    </select></div>
                            </div>


                        </div>
                        <div class="form-group mb-2">
                            <div class="row">
                                <div class="col-lg-4"><label for="maximum_allowed_clicks">Maximum Allowed Clicks:</label></div>
                                <div class="col-lg-6"><input type="text" class="form-control" id="maximum_allowed_clicks" name="maximum_allowed_clicks" value="{{ isset($existingFeatures['maximum_allowed_clicks']) ? $existingFeatures['maximum_allowed_clicks'] : '' }}"></div>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <div class="row mb-3">
                                <div class="col-lg-4"> <label for="maximum_click_monthly_add_on">Maximum Click Monthly Add-on:</label></div>
                                <div class="col-lg-6"><input type="text" class="form-control" id="maximum_click_monthly_add_on" name="maximum_click_monthly_add_on" value="{{ isset($existingFeatures['maximum_click_monthly_add_on']) ? $existingFeatures['maximum_click_monthly_add_on'] : '' }}"></div>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Features</button>
                    </form>
                </div>
            </div>
        </div>
    </div>




</div>
@endsection