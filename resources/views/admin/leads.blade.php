@extends('layouts.admin_template')

@section('content')

<div class="d-flex flex-column w-100">
    <div class="mb-2 mt-4">
        <h2 class="mt-2 mb-5">New Signups</h2>
    </div>

    <div class="top-row w-100">
        <div class="row">
            <div class="col-md-11">
                <form action="{{ route('admin.leads',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="">End Date</label>
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}" />
                        </div>
                        <div class="col-md-2">
                            <label for="per_page">Show</label>
                            <select name="per_page" id="per_page" class="form-select">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="search">Search</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search here...">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="input-group mt-4">
                                <button class="btn button-clearlink text-primary fw-bold" type="submit">Submit</button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

        </div>
        <div class="col-md-1">
            <form action="{{ route('admin.leads',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
                <div class="col-md-1">
                    <div class="input-group mt-2">
                        <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($leads->isNotEmpty())

    <div class="tables w-100">
        <table class="text-center mt-4 table table-bordered">
            <thead class="bg-clearlink fw-bold">
                <tr>
                    <th class="p-2">#</th>
                    <th class="p-2">Company Name</th>
                    <th class="p-2">User Name</th>
                    <th class="p-2">Email</th>
                    <th class="p-2">View Details</th>
                    <th class="p-2">Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($leads as $lead)
                @if($lead->status === 'Approved' || $lead->status === null)
                <tr>

                    <td>{{ $leads->total() - (($leads->currentPage() - 1) * $leads->perPage()) - $loop->index }}</td>

                    <td class="p-2">{{$lead->company_name}}</td>
                    <td class="p-2">{{$lead->first_name }}&nbsp;{{$lead->last_name}}</td>
                    <td class="p-2">{{$lead->email}}</td>
                    <td class="p-2">
                        <a href="view-lead/{{$lead->id}}" class="btn button-clearlink text-primary fw-bold">View Details</a>
                    </td>
                    @if($lead->status === null)
                    <td class="p-2"><a class="border-0 fw-bold bg-white me-3" data-toggle="tooltip" title="Approve" data-toggle="tooltip" title="Approve" href="approve-lead/{{$lead->id}}"><i class="fa-solid fa-check text-check fs-3"></i></a> <a class=" fw-bold" data-toggle="tooltip" title="Reject" href="reject-lead/{{$lead->id}}"><i class="fa-solid fa-xmark text-primary fs-3"></i></a></td>
                    @else
                    <td class="p-2"><span class="{{$lead->status === 'Approved' ? 'badge-success' : 'badge-fail ' }}">{{$lead->status}}</span></td>
                    @endif

                    <!-- <div class="modal fade" id="approveModal{{$lead->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-md modal-dialog-centered">
                            <div class="modal-content bg-popup">
                                <div class=" modal-header">
                                    <h3 class="modal-title " id="exampleModalLabel">Enter the required details</h3>
                                    <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                                </div>
                                <div class="modal-body">
                                    <form action="/approve-lead" method="post">
                                        @csrf
                                        <div class=" row">
                                            <label for="affiliate_id" class="form-label fw-bold">Select Affiliate Ids*</label>
                                            <select class="ms-2 form-control" id="affiliateDropdown{{$lead->id}}">
                                                <option value="">Select Affiliate Ids</option>
                                                @foreach($affiliates as $affiliate)
                                                <option value="{{$affiliate->isp_affiliate_id}}">{{$affiliate->isp_affiliate_id}}({{$affiliate->domain_name}})</option>
                                                @endforeach
                                                
                                            </select>
                                            <input class="ms-2 mb-2 fw-bold border-0 bg-popup" name="affiliate_id" id="selectedAffiliatesDisplay{{$lead->id}}" readonly />
                                        </div>
                                        <div class="mb-3 row">
                                            <div class="col-lg">
                                                <label for="advertiser_id" class="form-label fw-bold">Advertiser Id*</label>
                                                <input name="advertiser_id" class=" form-control" placeholder="Enter Advertiser Id*" required>
                                            </div>
                                            <input name="lead_id" value="{{$lead->id}}" hidden />
                                        </div>
                                        <input type="submit" class="btn btn-primary py-2 rounded " value="Save">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div> -->
                </tr>
                @endif

                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script>
                    $(document).ready(function() {
                        $('#affiliateDropdown{{$lead->id}}').change(function() {
                            var selectedAffiliate = $(this).val();

                            if (selectedAffiliate) {
                                console.log('selected');
                                // Append to textarea
                                var currentText = $('#selectedAffiliatesDisplay{{$lead->id}}').val();
                                var newText = currentText.length > 0 ? currentText + ',' + selectedAffiliate : selectedAffiliate;
                                $('#selectedAffiliatesDisplay{{$lead->id}}').val(newText);

                                // Remove selected option from dropdown
                                $(this).find('option[value="' + selectedAffiliate + '"]').remove();
                            }
                        });
                    });
                </script>
                @endforeach

            </tbody>
        </table>
    </div>
    <div class="mt-2 mb-5 paginate">
        <div class="row">
            <div class="col-md-12">
                <div class="pagination">
                    @if ($leads->lastPage() > 1)
                    <ul class="pagination">
                        <li class="{{ ($leads->currentPage() == 1) ? 'disabled' : '' }}">
                            <a href="{{ $leads->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
                        </li>
                        @for ($i = 1; $i <= $leads->lastPage(); $i++)
                            <li class="{{ ($leads->currentPage() == $i) ? 'active' : '' }}">
                                <a href="{{ $leads->appends(request()->query())->url($i) }}" class="page-link{{ ($leads->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
                            </li>
                            @endfor
                            <li class="{{ ($leads->currentPage() == $leads->lastPage()) ? 'disabled' : '' }}">
                                <a href="{{ $leads->appends(request()->query())->url($leads->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
                            </li>
                    </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>
    @else
    <div class="d-flex justify-content-center align-items-center mt-5">
        <h3>No Signups found.</h3>
    </div>
    @endif
</div>
@endsection