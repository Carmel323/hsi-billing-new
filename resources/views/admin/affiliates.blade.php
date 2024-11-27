@extends('layouts.admin_template')

@section('content')

<div class="d-flex flex-column  w-100">
    <div class="mb-2 mt-4 w-100">
        <div class="d-flex flex-row justify-content-between">

            <div>
                <h2 class="mt-2 mb-5">Affiliates</h2>
            </div>
            <div>
                <div class=" d-flex justify-content-center align-items-center mt-3 ">
                    <a data-bs-toggle="modal" data-bs-target="#affiliateModal" class="btn mb-1 btn-primary">Add an affiliate</a>
                </div>
            </div>
        </div>
    </div>

    <div class="top-row w-100">
        <div class="row">
            <div class="col-md-11">
                <form action="{{ route('admin.affiliates',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
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
            <form action="{{ route('admin.affiliates',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
                <div class="col-md-1">
                    <div class="input-group mt-2">
                        <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($affiliates->isNotEmpty())

    <div class="tables w-100">
        <table class="text-center mt-4 table table-bordered">
            <thead class="bg-clearlink fw-bold">
                <tr>
                    <th class="p-2">#</th>
                    <th class="p-2">Affiliate Id</th>
                    <th class="p-2">Domain Name</th>
                    <!-- <th class="p-2"></th>
                    <th class="p-2"></th> -->
                </tr>
            </thead>

            <tbody>
                @foreach($affiliates as $affiliate)
                <tr>
                    <td>{{ $affiliates->total() - (($affiliates->currentPage() - 1) * $affiliates->perPage()) - $loop->index }}</td>
                    <td class="p-2">{{$affiliate->isp_affiliate_id}}</td>
                    <td class="p-2">{{$affiliate->domain_name }}</td>
                    <!-- <td class="p-2"> <button data-bs-toggle="modal" data-bs-target="#editAffiliate{{$affiliate->isp_affiliate_id}}" class="btn btn-primary">Edit</button></td>
                    <td class="p-2">
                        <a href="/delete-affiliate/{{$affiliate->id}}" class="btn text-primary fw-bold button-clearlink">Delete</a>
                    </td> -->
                </tr>
                <div class="modal fade" id="editAffiliate{{$affiliate->isp_affiliate_id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content bg-popup">
                            <div class=" modal-header">
                                <h5 class="modal-title " id="exampleModalLabel">Edit Affiliate </h5>
                                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                            </div>
                            <div class="modal-body">
                                <form action="/edit-affiliate" method="post">
                                    @csrf
                                    <div>
                                        <div class=" mb-3 row">
                                            <div class="col-lg">
                                                <input name="affiliate_id" class="ms-2 form-control" type="number" value="{{$affiliate->isp_affiliate_id}}" required>
                                            </div>
                                            <div class="col-lg">
                                                <input name="domain_name" class="ms-2 form-control" value="{{$affiliate->domain_name}}" required>
                                            </div>
                                            <input name="id" value="{{$affiliate->id}}" hidden />
                                        </div>
                                    </div>
                                    <input type="submit" class="btn btn-primary px-3 py-2 rounded " value="Update">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

            </tbody>
        </table>
    </div>
    <div class="mt-2 mb-5 paginate">
        <div class="row">
            <div class="col-md-12">
                <div class="pagination">
                    @if ($affiliates->lastPage() > 1)
                    <ul class="pagination">
                        <li class="{{ ($affiliates->currentPage() == 1) ? 'disabled' : '' }}">
                            <a href="{{ $affiliates->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
                        </li>
                        @for ($i = 1; $i <= $affiliates->lastPage(); $i++)
                            <li class="{{ ($affiliates->currentPage() == $i) ? 'active' : '' }}">
                                <a href="{{ $affiliates->appends(request()->query())->url($i) }}" class="page-link{{ ($affiliates->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
                            </li>
                            @endfor
                            <li class="{{ ($affiliates->currentPage() == $affiliates->lastPage()) ? 'disabled' : '' }}">
                                <a href="{{ $affiliates->appends(request()->query())->url($affiliates->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
                            </li>
                    </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>
    @else
    <div class="d-flex justify-content-center align-items-center mt-5">
        <h3>No Affiliates found.</h3>
    </div>
    @endif
</div>
<div class="modal fade" id="affiliateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content bg-popup">
            <div class=" modal-header">
                <h3 class="modal-title " id="exampleModalLabel">Add Affiliate</h3>
                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body">
                <form action="/add-affiliate" method="post">
                    @csrf

                    <div class="mb-3 row">
                        <div class="col-lg-12">
                            <label for="affiliate_id" class="form-label fw-bold">Affiliate Id*</label>
                            <input type="number" name="affiliate_id" class="form-control" value="" required>
                        </div>
                        <div class="col-lg-12 mt-2">
                            <label for="domain_name" class="form-label fw-bold">Domain Name</label>
                            <input type="text" name="domain_name" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary px-3 py-2 rounded">Add Affiliate</button>
            </div>
        </div>
    </div>
</div>


@endsection