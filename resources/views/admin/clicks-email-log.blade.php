@extends('layouts.admin_template')

@section('content')

<div class="d-flex flex-column w-100">
    <div class="mb-2 mt-4">
        <h2 class="mt-2 mb-5">Clicks Email Log </h2>
    </div>

    <div class="top-row w-100">
        <div class="row">
            <div class="col-md-11">
                <form action="{{ route('admin.clicks-email-log',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
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
            <form action="{{ route('admin.clicks-email-log',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
                <div class="col-md-1">
                    <div class="input-group mt-2">
                        <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($clicks_email_log->isNotEmpty())

    <div class="tables w-100">
        <table class="text-center mt-4 table table-bordered">
            <thead class="bg-clearlink fw-bold">
                <tr>
                    <th class="p-2">#</th>
                    <th class="p-2">Parnter ID</th>
                    <th class="p-2">Clicks Month</th>
                    <th class="p-2">Clicks Year</th>
                    <th class="p-2">Details</th>
                    <th class="p-2">Timestamp</th>
                </tr>
            </thead>

            <tbody>
                @foreach($clicks_email_log as $log)
                <tr>
                    <td>{{ $clicks_email_log->total() - (($clicks_email_log->currentPage() - 1) * $clicks_email_log->perPage()) - $loop->index }}</td>
                    <td class="p-2">
                        {{$log->partner_id}}
                    </td>
                    <td class="p-2">{{$log->clicks_month}}</td>
                    <td class="p-2">{{$log->clicks_year}}</td>
                    <td class="p-2">{{$log->details}}</td>
                    <td class="p-2">{{$log->timestamp}}</td>
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
    <div class="mt-2 mb-5 paginate">
        <div class="row">
            <div class="col-md-12">
                <div class="pagination">
                    @if ($clicks_email_log->lastPage() > 1)
                    <ul class="pagination">
                        <li class="{{ ($clicks_email_log->currentPage() == 1) ? 'disabled' : '' }}">
                            <a href="{{ $clicks_email_log->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
                        </li>
                        @for ($i = 1; $i <= $clicks_email_log->lastPage(); $i++)
                            <li class="{{ ($clicks_email_log->currentPage() == $i) ? 'active' : '' }}">
                                <a href="{{ $clicks_email_log->appends(request()->query())->url($i) }}" class="page-link{{ ($clicks_email_log->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
                            </li>
                            @endfor
                            <li class="{{ ($clicks_email_log->currentPage() == $clicks_email_log->lastPage()) ? 'disabled' : '' }}">
                                <a href="{{ $clicks_email_log->appends(request()->query())->url($clicks_email_log->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
                            </li>
                    </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>
    @else
    <div class="d-flex justify-content-center align-items-center mt-5">
        <h3>No Log found.</h3>
    </div>
    @endif
</div>

@endsection