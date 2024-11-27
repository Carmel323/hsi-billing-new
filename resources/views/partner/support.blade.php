@extends('layouts.partner_template')

@section('content')

<div class="d-flex flex-column justify-content-center align-items-center w-100">
    <div class="mb-2 mt-2 w-100">
        <div class="row">
            <div class="col-md-6">
                <h2 class=" mb-5">Support Tickets</h2>
            </div>
            <div class="col-md-6">
                <div class=" d-flex justify-content-end align-items-end ">
                    <a type="button" class="btn btn-primary button-padding" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        Create New Ticket
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-dark bg-popup">
                <div class="modal-header border-0">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Enter the request message</h1>
                    <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                </div>
                <div class="modal-body p-0">
                    <form action="/custom-support" method="post">
                        @csrf
                        <label class="fw-bold">Message*</label>
                        <textarea class="w-100 p-3 pe-4 border-0 rounded" name="message"></textarea>

                        <input type="submit" class="btn btn-primary popup-element" value="Submit">
                </div>
                <div class="modal-footer border-0">

                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="top-row w-100">
        <div class="row">
            <div class="col-md-11">
                <form action="{{ route('partner.support',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold">End Date</label>
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}" />
                        </div>
                        <div class="col-md-2">
                            <label class="fw-bold" for="per_page">Show:</label>
                            <select name="per_page" id="per_page" class="form-select">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10
                                </option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25
                                </option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50
                                </option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold" for="search">Search</label>
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
            <form action="{{ route('partner.support',[],false) }}" method="GET" class="row align-items-center w-100">
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
            <thead class="text-dark fw-bold bg-clearlink">
                <tr>
                    <th class="p-2">#</th>
                    <th class="p-2">Date</th>
                    <th class="p-2">Request type</th>
                    <th class="p-2">Subscription Number</th>
                    <th class="p-2">Company Name</th>
                    <th class="p-2">Message</th>
                    <th class="p-2">Status</th>
                </tr>
            </thead>
            <tbody>

                @foreach($supports as $support)
                <tr>

                    <td>{{ $supports->total() - (($supports->currentPage() - 1) * $supports->perPage()) -
                            $loop->index }}</td>

                    <td class="p-2" data-label="Support Date">
                        {{ \Carbon\Carbon::parse($support->date)->format('d-M-Y') }}
                    </td>
                    <td class="p-2" data-label="Request type">{{ $support->request_type }}</td>
                    <td class="p-2" data-label="Subscription ID">{{ $support->subscription_number }}</td>
                    <td class="p-2" data-label="Partner Email">{{ $support->partner->company_name }}</td>
                    <td class="p-2" data-label="Message">{{ $support->message }}</td>
                    <td class="p-2" data-label="Status">
                        @if($support->status ==='open')
                        <span class="badge-success">Open</span>
                        @elseif($support->status ==='Completed')
                        <span class="badge-fail">Closed</span>
                        @elseif($support->status === 'Revoked')
                        <span class="badge-revoked">{{ $support->status }}</span>
                        @endif
                    </td>
                </tr>
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
        <h3>No Support Tickets found.</h3>
    </div>
    @endif
</div>

@include('layouts.show-alert-modal')
@endsection