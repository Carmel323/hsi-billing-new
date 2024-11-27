@extends('layouts.view-lead-template')

@section('child-content')
<div style="width:80%" class="d-flex flex-row justify-content-between mt-5">
    <div>
        <h5 class="fw-bold">Overview</h5>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 mb-4 bg-clearlink">
            <div class="card-body">
                <h4 class="right-margin">Lead Details</h4>

                <div class="d-flex flex-row justify-content-between right-margin">
                    <div class="col-lg-10 ">
                        <div class="d-flex flex-row justify-content-between">
                            <div class="d-flex flex-row ">
                                <div class="m-0"><i class="fa text-primary fa-building  fw-bold" aria-hidden="true"></i></div>
                                <div class="right-margin"><strong>{{ $lead->company_name ?? '' }}</strong><br>{{ $lead->first_name ?? '' }}&nbsp;{{ $lead->last_name ?? '' }}<br>{{ $lead->email ?? '' }}<br>{{ $lead->phone_number ?? '' }}</div>
                            </div>
                        </div>
                    </div>


                </div>






                <div class="accordion accordion-flush border-0 shadow-none right-margin mb-0 " id="accordionExample">
                    <div class="accordion-item bg-clearlink">
                        <h2 class="accordion-header">
                            <button class="accordion-button p-0 fw-bold border-0 outline-0 border-bottom fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                <h4> Address</h4>
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                            <div class="accordion-body p-0 pt-2 ">
                                <div class="d-flex flex-row justify-content-between">
                                    <div class="d-flex flex-row ">
                                        <div class="m-0"><i class="fa fa-address-card  text-primary" aria-hidden="true"></i></div>
                                        <div class="right-margin"><strong> Billing Address</strong><br>{{ $lead->street ?? '' }}<br>{{ $lead->city ?? '' }},<br>{{ $lead->state ?? '' }},<br>United States,<br>{{ $lead->zip_code ?? '' }}.</div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>



            </div>
        </div>
    </div>


</div>
@endsection