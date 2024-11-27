@extends('layouts.partner_template')

@section('content')

<div class="d-flex flex-column justify-content-center align-items-center w-100">
    <div class="mb-2 w-100">
        <h2 class="mt-2 mb-5">Credit Notes</h2>
    </div>

    <div class="top-row w-100">
        <div class="row">
            <div class="col-md-11">
                <form action="{{ route('partner.creditnotes', [], false) }}" method="GET" class="row g-3 align-items-center w-100">
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
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
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
            <form action="{{ route('partner.creditnotes', [], false) }}" method="GET" class="row g-3 align-items-center w-100">
                <div class="col-md-1">
                    <div class="input-group mt-2">
                        <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($creditnotes->isNotEmpty())

    <div class="tables w-100">
        <table class="text-center mt-5 table table-bordered">
            <thead class="fw-bold bg-clearlink ">
                <tr>
                    <th class="p-2">#</th>
                    <th class="p-2">Date</th>
                    <th class="p-2">Credit Note#</th>
                    <th class="p-2">Company Name</th>
                    <th class="p-2">Invoice Number</th>
                    <th class="p-2">Credited Amount(USD)</th>
                    <th class="p-2">Balance(USD)</th>
                    <th class="p-2">Status</th>
                    <th class="p-2">View</th>

                </tr>
            </thead>

            <tbody id="creditnotesTable">
                @foreach($creditnotes as $credit)
                <tr>
                    <td>{{ $creditnotes->total() - (($creditnotes->currentPage() - 1) * $creditnotes->perPage()) - $loop->index }}</td>
                    <td class="p-2" data-label="Date">{{ Carbon\Carbon::parse($credit->credited_date)->format('d-M-Y') }}</td>
                    <td class="p-2" data-label="Credit Note#">{{ $credit->creditnote_number }}</td>
                    <td class="p-2" data-label="Partner Name">{{ $credit->partner->company_name }}</td>
                    <td class="p-2" data-label="Invoice Number">{{ $credit->invoice_number }}</td>

                    <td class="p-2" data-label="Credited Amount(USD)">{{ $credit->credited_amount }}</td>

                    <td class="p-2" data-label="Balance(USD)">{{ $credit->balance }}</td>

                    <td class="p-2 status" data-label="Status"><span class="{{$credit->status === 'open'?'badge-success  ':'badge-fail '}}">{{ $credit->status }}</span></td>

                    <td class="p-2 "><a href="/view-creditnote/{{ $credit->creditnote_id }}" class="btn btn-sm btn-primary">Download PDF</a></td>

                </tr>
                <div class=" modal fade" id="creditModal{{ $credit->creditnote_id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-popup ">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Enter the Required Details</h1>
                                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                            </div>
                            <div class="modal-body">
                                <form action="/refund-credit" method="post">
                                    @csrf
                                    <input type="text" name="id" value="{{ $credit->creditnote_id }}" hidden />
                                    <input type="text" class="popup-element" name="balance_amount" value="{{ $credit->balance }}" hidden />
                                    <div class="row">
                                        <input type="text" name="refund_amount" placeholder="Refund Amount" class="form-control popup-element col-lg m-2" required />
                                        <input type="text" name="reason" placeholder="Reason" class="form-control popup-element col-lg m-2" required />
                                    </div>
                            </div>
                            <div class="modal-footer"><input type="submit" class="btn btn-primary popup-element" value="Save">
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
                    @if ($creditnotes->lastPage() > 1)
                    <ul class="pagination">
                        <li class="{{ ($creditnotes->currentPage() == 1) ? 'disabled' : '' }}">
                            <a href="{{ $creditnotes->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
                        </li>
                        @for ($i = 1; $i <= $creditnotes->lastPage(); $i++)
                            <li class="{{ ($creditnotes->currentPage() == $i) ? 'active' : '' }}">
                                <a href="{{ $creditnotes->appends(request()->query())->url($i) }}" class="page-link{{ ($creditnotes->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
                            </li>
                            @endfor
                            <li class="{{ ($creditnotes->currentPage() == $creditnotes->lastPage()) ? 'disabled' : '' }}">
                                <a href="{{ $creditnotes->appends(request()->query())->url($creditnotes->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
                            </li>
                    </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="d-flex justify-content-center align-items-center mt-5">
        <h3>No Credit Notes found.</h3>
    </div>
    @endif
</div>

@include('layouts.show-alert-modal')

@endsection