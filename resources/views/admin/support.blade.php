@extends('layouts.admin_template')

@section('content')

<div class="d-flex flex-column w-100">
    <div class="mb-2 mt-4">
        <h2 class="mt-2 mb-5">Supports Tickets</h2>
    </div>

    <div class="top-row w-100">
        <div class="row">
            <div class="col-md-11">
                <form action="{{ route('admin.supports',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
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
            <form action="{{ route('admin.supports',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
                <div class="col-md-1">
                    <div class="input-group mt-2">
                        <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($supports->isNotEmpty())

    <div class="tables w-100">
        <table class="text-center mt-4 table table-bordered">
            <thead class="bg-clearlink">
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Request Type</th>
                    <th>Subscription Number</th>
                    <th>Company Name</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Comments</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($supports as $index => $support)
                <tr>
                    <td>{{ $supports->total() - (($supports->currentPage() - 1) * $supports->perPage()) - $loop->index }}</td>
                    <td>{{ $support->date }}</td>
                    <td>{{ $support->request_type }}</td>
                    <td>{{ $support->subscription_number }}</td>
                    <td>{{ $support->partner->company_name }}</td>
                    <td>{{ $support->message }}</td>
                    <td> @if($support->status ==='open')
                        <span class="badge-success">Open</span>
                        @elseif($support->status ==='Completed')
                        <span class="badge-fail">Closed</span>
                        @elseif($support->status === 'Revoked')
                        <span class="badge-revoked">{{ $support->status }}</span>
                        @endif
                    </td>
                    <td>
                        @if($support->comments)
                        {{ $support->comments }}
                        @else
                        <a href="#" data-bs-toggle="modal" data-bs-target="#revokeModal{{ $support->id }}" class="btn btn-primary">Revoke</a>
                        @endif
                    </td>
                    <td>
                        @if($support->status === 'open')
                        <form action="/support" method="post">
                            @csrf
                            <input type="hidden" name="id" value="{{ $support->id }}">
                            <button class="btn button-clearlink text-primary fw-bold" type="submit">Close</button>
                        </form>
                        @else
                        <span>{{$support->status}}</span>
                        @endif
                    </td>
                </tr>


                <div class="modal fade" id="revokeModal{{ $support->id }}" tabindex="-1" aria-labelledby="revokeModalLabel{{ $support->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-popup">
                            <div class="modal-header">
                                <h3 class="modal-title" id="revokeModalLabel{{ $support->id }}">Reason to Revoke</h3>
                                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>

                            </div>
                            <div class="modal-body">
                                <form action="/revoke-support" method="post">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $support->id }}">
                                    <textarea class="form-control mb-4" name="comments" required></textarea>

                                    <button type="submit" class="btn btn-primary">Save</button>
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
                    @if ($supports->lastPage() > 1)
                    <ul class="pagination">
                        <li class="{{ ($supports->currentPage() == 1) ? 'disabled' : '' }}">
                            <a href="{{ $supports->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
                        </li>
                        @for ($i = 1; $i <= $supports->lastPage(); $i++)
                            <li class="{{ ($supports->currentPage() == $i) ? 'active' : '' }}">
                                <a href="{{ $supports->appends(request()->query())->url($i) }}" class="page-link{{ ($supports->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
                            </li>
                            @endfor
                            <li class="{{ ($supports->currentPage() == $supports->lastPage()) ? 'disabled' : '' }}">
                                <a href="{{ $supports->appends(request()->query())->url($supports->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
                            </li>
                    </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>
    @else
    <div class="d-flex justify-content-center align-items-center mt-5">
        <h3>No Support Tickets raised.</h3>
    </div>
    @endif
</div>

@endsection