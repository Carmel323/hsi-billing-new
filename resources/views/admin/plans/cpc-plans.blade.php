@extends('layouts.view-plans-template')

@section('child-content')

<div class="d-flex flex-column w-100">

    @if($plans->isNotEmpty())

    <div class="tables w-100">
        <table class="text-center mt-2 table table-bordered">
            <thead class="bg-clearlink fw-bold">
                <tr>
                    <th>S.No</th>
                    <th>Plan Name</th>
                    <th>Plan Price</th>
                    <th>Interval</th>
                    <th>Interval Unit</th>
                    <th>Number of Plan Subscriptions</th>
                    <th>Max Allowed Clicks(Plan)</th>
                    <th>Add-On Name</th>
                    <th>Add-On Price</th>
                    <th>Number of Add-On Subscriptions</th>
                    <th>Max Allowed Clicks(Add-On)</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($plans as $index => $plan)
                <tr>
                    <td>{{$index+1}}</td>
                    <td>{{$plan->plan_name}}</td>
                    <td>{{$plan->price}}</td>
                    <td>{{$plan->interval}}</td>
                    <td>{{$plan->interval_unit}}</td>
                    <td>{{$plan->count}}</td>
                    <td>{{$plan->max_clicks}}</td>
                    @php
                    $add_ons = DB::table('add_ons')->where('plan_id',$plan->plan_id)->get();
                    @endphp

                    <td>
                        <ul>
                            @foreach($add_ons as $add_on)
                            <li>{{$add_on->name}}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>
                        <ul>
                            @foreach($add_ons as $add_on)
                            <li>{{$add_on->addon_price}}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>{{$plan->addon_count}}</td>
                    <td>
                        <ul>
                            @foreach($add_ons as $add_on)
                            <li>{{$add_on->max_clicks}}</li>
                            @endforeach
                        </ul>
                    </td>

                    @if($plan->price !== '0')

                    <td><a type="button" class="btn button-clearlink text-primary fw-bold" href="{{ route('admin.planfeatures.show',['plan_code' => $plan->plan_code],false) }}">Edit/View </a></td>
                    <td style="display:none;">
                        <a type="button" class="btn button-clearlink text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#{{$plan->plan_id}}">Edit/View </a>

                        <div class="modal fade" id="{{$plan->plan_id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content bg-popup">
                                    <div class="modal-table ">
                                        <h3 class="modal-title fs-5" id="staticBackdropLabel">{{$plan->plan_name}}</h3>
                                        <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                        $plan_name = strtolower(str_replace(' ', '', $plan->plan_name))
                                        ?>


                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    @else
                    <td>-</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="d-flex flex-column justify-content-center align-items-center">
            <span class="d-block">No plans found. Click on the button below to add plans.</span>
            <a href="/sync-plans" class="btn btn-primary text-white text-center ms-5 mt-3 px-3 py-2 rounded">Add Plans</a>
            @endif
        </div>

    </div>
    <div class="modal fade" id="add-plans" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-popup">
                <div class="modal-table">
                    <h3 class="modal-title" id="staticBackdropLabel"><strong> Kindly fill the required details</strong></h3>
                    <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                </div>
                <div class="modal-body">
                    <form action="/add-plans" method="post">
                        @csrf
                        <div class="mb-3 row">
                            <div class="col-lg-12">
                                <label for="name" class="form-label fw-bold">Plan Name*</label>
                                <input type="text" name="name" id="plan_name" class="form-control" value="" required>
                            </div>
                            <div class="col-lg-12 mt-2">
                                <label for="plan_code" class="form-label fw-bold">Plan Code</label>
                                <input type="text" name="plan_code" class="form-control" required readonly>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-lg">
                                <label for="recurring_price" class="form-label fw-bold">Plan Price in USD*</label>
                                <input type="text" name="recurring_price" class="form-control" required>
                            </div>
                            <div class="col-lg">
                                <label for="plan_price" class="form-label fw-bold">Bill Every*</label>
                                <div class="row">
                                    <div class="col-lg-4 mb-2">
                                        <input type="text" name="interval" class="form-control" value="1" required>
                                    </div>
                                    <div class="col-lg-8">
                                        <select type="text" name="interval_unit" class="form-select" required>
                                            <option value="months">Month(s)</option>
                                            <option value="weeks">Year(s)</option>
                                            <option value="years">Week(s)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="billing_cycles" class="form-label fw-bold">Billing Cycles*</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="billing_cycles" id="autoRenew" value="-1" onclick="hideTextbox()" checked>
                                <label class="form-check-label fw-bold" for="autoRenew">Auto-renews until canceled</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="billing_cycles" id="expires" value="1" onclick="showTextbox()">
                                <label class="form-check-label fw-bold" for="expires">Expires after a specified no. of billing cycles</label>
                            </div>
                            <input style="display: none;" type="number" name="billing_cycles_no" id="textbox" class="form-control" placeholder="Enter the no. of billing cycles">
                        </div>

                        <button type="submit" class="btn btn-primary px-3 py-2 rounded">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @endsection