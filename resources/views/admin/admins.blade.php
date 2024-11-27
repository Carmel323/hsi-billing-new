@extends('layouts.admin_template')

@section('content')

<div class="d-flex flex-column justify-content-center align-items-center w-100">
    <div class="mb-2 mt-4 w-100">
        <div class="d-flex flex-row justify-content-between">
            <div>
                <h2 class="mt-2 mb-5">Admins</h2>
            </div>
            <div>
                <div class=" d-flex justify-content-center align-items-center mt-3 ">
                    <a data-bs-toggle="modal" data-bs-target="#exampleModal" class="btn mb-1 btn-primary">Invite an Admin</a>
                </div>
            </div>
        </div>
    </div>

    <div class="top-row w-100 mb-4 mt-4">

        <div class="row">
            <div class="col-md-11">
                <form action="{{ route('admin.admins',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
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
            <form action="{{ route('admin.admins',[],false) }}" method="GET" class="row g-3 align-items-center w-100">
                <div class="col-md-1">
                    <div class="input-group mt-2">
                        <button class="btn text-primary text-decoration-underline fw-bold p-0 pt-2" type="submit">Reset</button>
                    </div>
                </div>
            </form>
        </div>

    </div>

    @if($admins->isNotEmpty())

    <div class="tables w-100">



        <table class="text-center table table-bordered">
            <thead class="bg-clearlink fw-bold">
                <tr>
                    <th>S.No</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Mail Notifications</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach($admins as $index => $admin)
                <tr>
                    <td>{{ $admins->total() - (($admins->currentPage() - 1) * $admins->perPage()) - $loop->index }}</td>
                    <td data-label="Name">{{$admin->admin_name}}</td>
                    <td data-label="Email">{{$admin->email}}</td>
                    <td data-label="Role">{{$admin->role}}</td>
                    <td data-label="Mail Notifications">{{$admin->receive_mails}}</td>
                    <td data-label="Change Role"><button data-bs-toggle="modal" data-bs-target="#editAdmin{{$admin->id}}" class="btn btn-primary">Edit</button></td>
                    <td data-label="Delete"><button data-bs-toggle="modal" data-bs-target="#deleteModal{{$admin->id}}" class="btn button-clearlink text-primary fw-bold">Delete</button></td>
                </tr>
                <div class="modal fade" id="editAdmin{{$admin->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content bg-popup">
                            <div class=" modal-header">
                                <h3 class="modal-title " id="exampleModalLabel">Edit Admin Details</h3>
                                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                            </div>
                            <div class="modal-body">
                                <form action="/update-admin" method="post">
                                    @csrf
                                    <div>
                                        <div class=" mb-3 row">
                                            <div class="col-lg">
                                                <input name="admin_name" class="ms-2 form-control" placeholder="Admin Name*" value="{{$admin->admin_name}}" required>
                                            </div>
                                            <div class="col-lg">
                                                <input name="email" class="ms-2 form-control" placeholder="Email*" value="{{$admin->email}}" required readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <div class="col-lg">
                                            <select type="text" name="role" class="form-select ms-2" required>
                                                <option value="">Select Role*</option>
                                                <option value="Admin" {{ $admin->role === 'Admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="SuperAdmin" {{ $admin->role === 'SuperAdmin' ? 'selected' : '' }}>Super Admin</option>
                                            </select>
                                        </div>
                                        <div class="col-lg ">
                                            @php
                                            $isChecked = $admin->receive_mails === 'Yes'? true : false ;
                                            @endphp
                                            <label for="receive_mails" class="checkbox-inline ms-4">
                                                Receive Mail Notifations* <input type="checkbox" name="receive_mails" class="form-check-input ms-4" {{ $isChecked ? 'checked' : '' }}>
                                            </label>
                                        </div>
                                    </div>
                                    <input name="id" class="ms-2 form-control" value="{{$admin->id}}" required hidden>
                                    <input type="submit" class="btn btn-primary text-white px-3 py-2 rounded " value="Update">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="deleteModal{{$admin->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered ">
                        <div class="modal-content bg-popup">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Do you really want to delete the admin?</h1>
                                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                            </div>
                            <div class="modal-footer">
                                <a href="/delete-admin/{{$admin->id}}" type="button" class="btn btn-primary">Proceed</a>
                                <button type="button" data-bs-dismiss="modal" class="btn button-clearlink text-primary fw-bold">Cancel</button>
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
                    @if ($admins->lastPage() > 1)
                    <ul class="pagination">
                        <li class="{{ ($admins->currentPage() == 1) ? 'disabled' : '' }}">
                            <a href="{{ $admins->appends(request()->query())->url(1) }}" class="page-link">{{ __('First') }}</a>
                        </li>
                        @for ($i = 1; $i <= $admins->lastPage(); $i++)
                            <li class="{{ ($admins->currentPage() == $i) ? 'active' : '' }}">
                                <a href="{{ $admins->appends(request()->query())->url($i) }}" class="page-link{{ ($admins->currentPage() == $i) ? ' active' : '' }}">{{ $i }}</a>
                            </li>
                            @endfor
                            <li class="{{ ($admins->currentPage() == $admins->lastPage()) ? 'disabled' : '' }}">
                                <a href="{{ $admins->appends(request()->query())->url($admins->lastPage()) }}" class="page-link">{{ __('Last') }}</a>
                            </li>
                    </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>
    @else
    <div class="d-flex justify-content-center align-items-center mt-5">
        <h3>No Admins found</h3>
    </div>
    @endif
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content bg-popup">
            <div class=" modal-header">
                <h5 class="modal-title " id="exampleModalLabel">Enter Admin Details</h5>
                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body">
                <form action="/invite-admin" method="post">
                    @csrf
                    <div>
                        <div class=" mb-3 row">
                            <div class="col-lg">
                                <input name="admin_name" class="ms-2 form-control" placeholder="Admin Name*" required>
                            </div>
                            <div class="col-lg">
                                <input name="email" class="ms-2 form-control" placeholder="Email*" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-lg">
                            <select type="text" name="role" class="form-select ms-2" required>
                                <option value="">Select Role*</option>
                                <option value="Admin">Admin</option>
                                <option value="SuperAdmin">Super Admin</option>
                            </select>
                        </div>
                        <div class="col-lg ">
                            <label for="receive_mails" class="checkbox-inline ms-4">
                                Receive Mail Notifations* <input type="checkbox" name="receive_mails" class="form-check-input ms-4">
                            </label>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-primary px-3 py-2 rounded " value="Save Changes">
                </form>
            </div>
        </div>
    </div>
</div>



@endsection