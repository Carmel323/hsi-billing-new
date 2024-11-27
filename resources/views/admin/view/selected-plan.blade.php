@extends('layouts.view-partner-template')

@section('child-content')
@if($plans->isNotEmpty())
<div style="width:80%" class="d-flex flex-row justify-content-between mt-5">
    <div>
        <h5 class="fw-bold">Plans</h5>
    </div>
</div>
<div class="tables w-100">
    <table class="text-center mt-2 table table-bordered partner-card mb-5">
        <thead class="bg-clearlink fw-bold">
            <tr>
                <th>S.No</th>
                <th>Plan Name</th>
                <th>Price</th>
                <th>Select</th>
            </tr>
        </thead>

        <tbody>

            <tr>
                <td>1</td>
                <td>Custom Enterprise</td>
                <td>Contact Us</td>
                <form method="post" id="yourForm" action="/add-selected-plans">
                    @csrf
                    <td>
                        @if($selected_plans)
                        <input type="checkbox" name="options[]" value="custom_plans" class="form-check-input select-plans-input" {{  in_array( 'custom_plans',$selected_plans) ? 'checked' : '' }} {{ $is_enterprise_plan ?'disabled' : '' }}>
                        @else
                        <input type="checkbox" name="options[]" value="custom_plans" class="form-check-input select-plans-input" />
                        @endif

                    </td>
            </tr>
            @foreach($plans as $index => $plan)
            <tr>
                <td>{{$index+2}}</td>
                <td>{{$plan->plan_name}}</td>
                <td>{{$plan->price}}</td>

                <td>


                    @if($selected_plans)
                    <input type="checkbox" name="options[]" value="{{$plan->plan_id}}" class="form-check-input select-plans-input" {{ in_array( $plan->plan_id,$selected_plans) ? 'checked' : '' }} {{isset($current_plan) &&  $current_plan->plan_id === $plan->plan_id ? 'disabled' : '' }} />
                    @else
                    <input type="checkbox" name="options[]" value="{{$plan->plan_id}}" class="form-check-input select-plans-input" />
                    @endif
                </td>


            </tr>
            @endforeach

            <tr>
                <td></td>
                <td></td>
                <td><input name="partner_id" value="{{$partner->id}}" hidden /></td>
                <td><input type="submit" class="btn btn-primary btn-sm" value="Submit" /></td>
                </form>
            </tr>
        </tbody>
    </table>
    @else
    <div class="d-flex flex-column justify-content-center align-items-center partner-card">
        <span class="d-block">No plans found.</span>
        @endif
    </div>

    <!-- <div>
        <form method="post" class="d-flex flex-row mt-3" action="/select-custom-plans">
            <label for="update_logo" class="checkbox-inline fw-bold">
                <span class="me-3">Custom Plans</span> <input type="checkbox" name="custom_plans" class="form-check-input" {{ isset($selected_plans['custom_plan']) && $selected_plans['custom_plan'] ? 'checked' : '' }}>
            </label>
            <input type="submit" class="btn btn-primary btn-sm ms-5" value="Submit" /></td>
        </form>
    </div> -->

</div>


@endsection