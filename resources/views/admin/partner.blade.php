@extends('layouts.admin_template')

@section('content')

<div class="d-flex flex-column  w-100">
  <div class="mb-2 mt-4 w-100">
    <div class="d-flex flex-row justify-content-between">

      <div>
        <h2 class="mt-2 mb-5">Partners</h2>
      </div>
      <div>
        <div class=" d-flex justify-content-center align-items-center mt-3 ">
          <a href="/admin/invite-partner" class="btn mb-1 btn-primary">Invite a Partner</a>
        </div>
      </div>
    </div>
  </div>

  <div class="top-row w-100 mb-4 mt-4">

    <div class="row">
      <div class="col-md-11">
        <form action="{{ route('admin.partners',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
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
      <form action="{{ route('admin.partners',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
        <div class="col-md-1">
          <div class="input-group mt-2">
            <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
          </div>
        </div>
      </form>
    </div>

  </div>

  @if($partners->isNotEmpty())

  <div class="tables w-100">

    <table class="text-center table table-bordered">
      <thead class="bg-clearlink fw-bold">
        <tr>
          <th>S.No</th>
          <th>Company</th>
          <th>Status</th>
          <th>Details</th>
        </tr>
      </thead>

      <tbody>
        @foreach($partners as $index => $partner)
        <tr>
          <td>{{ $partners->total() - (($partners->currentPage() - 1) * $partners->perPage()) - $loop->index }}</td>

          <td data-label="Company">{{$partner->company_name}}</td>
          <td data-label="Status" class="status">
            @if($partner->status ==='active')
            <span class="badge-warning">Setup In Progress</span>
            @elseif($partner->status ==='inactive')
            <span class="badge-fail">{{ $partner->status }}</span>
            @elseif($partner->status === 'Invited')
            <span class="badge-revoked">{{ $partner->status }}</span>
            @elseif($partner->status === 'completed')
            <span class="badge-success p-1 status ms-3 mb-2">Setup Completed</span>
            @endif
          </td>
          <td><a href="view-partner/{{$partner->id}}" class="btn button-clearlink text-primary fw-bold">View Details</a></td>
          @endforeach
      </tbody>
    </table>
  </div>
  <div class="mt-2 mb-5 paginate">
    <div class="row">
      <div class="col-md-12">
        <div class="pagination">
          @if ($partners->lastPage() > 1)
          <ul class="pagination">
            <li class="{{ ($partners->currentPage() == 1) ? 'disabled' : '' }}">
              <a href="{{ $partners->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
            </li>
            @for ($i = 1; $i <= $partners->lastPage(); $i++)
              <li class="{{ ($partners->currentPage() == $i) ? 'active' : '' }}">
                <a href="{{ $partners->appends(request()->query())->url($i) }}" class="page-link{{ ($partners->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
              </li>
              @endfor
              <li class="{{ ($partners->currentPage() == $partners->lastPage()) ? 'disabled' : '' }}">
                <a href="{{ $partners->appends(request()->query())->url($partners->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
              </li>
          </ul>
          @endif
        </div>
      </div>
    </div>

  </div>
  @else
  <div class="d-flex justify-content-center align-items-center mt-5">
    <h3>No Partners found</h3>
  </div>
  @endif
</div>
<!-- <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content bg-popup">
      <div class=" modal-header">
        <h3 class="modal-title " id="exampleModalLabel">Partner Details</h3>
        <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
      </div>
      <div class="modal-body">
        <form action="/invite-partner" method="post">
          @csrf
          <h4 class=" mb-4">Company Details</h4>
          <div class="mb-3 row">
            <div class="col-lg">
              <input name="company_name" class="ms-2 form-control" placeholder="Company Name*" required>
            </div>
            <div class="col-lg">
              <input name="tax_number" class="ms-2 form-control" placeholder="Tax Number*" required>
            </div>
          </div>
          <div class="mb-3 row">
            <div class="col-lg">
              <select class="ms-2 form-control" id="affiliateDropdown" required>
                <option id="select" value="">Select Affiliate Ids*</option>
                @foreach($affiliates as $affiliate)
                <option value="{{$affiliate->isp_affiliate_id}}({{$affiliate->domain_name}})">{{$affiliate->isp_affiliate_id}}({{$affiliate->domain_name}})</option>
                @endforeach

              </select>
              <textarea class="ms-2 mb-2 fw-bold border-0 bg-popup w-100" name="affiliate_id" id="selectedAffiliatesDisplay" readonly></textarea>
            </div>
            <div class="col-lg">
              <input name="advertiser_id" class="ms-2 form-control" placeholder="Advertiser ID*" required>
            </div>
          </div>
          <hr class="">
          <h4 class=" mb-4">Primary Contact Details</h4>
          <div>
            <div class=" mb-3 row">
              <div class="col-lg">
                <input name="first_name" class="ms-2 form-control" placeholder="First Name*" required>
              </div>
              <div class="col-lg">
                <input name="last_name" class="ms-2 form-control" placeholder="Last Name*" required>
              </div>
            </div>
          </div>

          <div class="mb-3 row">
            <div class="col-lg">
              <input name="email" class="ms-2 form-control" placeholder="Email*" required>
            </div>
            <div class="col-lg">
              <input name="phone_number" class="ms-2 form-control" placeholder="Phone Number*" required>
            </div>
          </div>

          <hr class="">
          <h4 class=" mb-4">Company Address Details</h4>

          <div class="mb-3 row">
            <div class="col-lg">
              <input name="address" class="ms-2 form-control" placeholder="Address*">
            </div>
            <div class="col-lg">
              <input name="city" class="ms-2 form-control" placeholder="City*">
            </div>
          </div>
          <div class="mb-3 row">
            <div class="col-lg">
              <input name="state" class="ms-2 form-control" placeholder="State*">
            </div>
            <div class="col-lg">
              <input name="zip_code" class="ms-2 form-control" placeholder="Zip Code*">
            </div>
          </div>
          <input name="country" class="ms-2 form-control" value="United States" hidden>
          <input type="submit" class="btn btn-primary  px-3 py-2 rounded " value="Save Changes">
        </form>
      </div>
    </div>
  </div>
</div> -->

@endsection