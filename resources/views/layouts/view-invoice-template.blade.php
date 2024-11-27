@extends('layouts.admin_template')

@section('content')
<div class="d-flex mt-4 flex-row ">
    <h2 class="mt-2 mb-3">Invoices</h2>
</div>

<nav class="navbar1 navbar-expand-lg border-bottom border-dark">
    <div class="container-fluid">

        <ul class="navbar-nav">
            <li class="nav-item m-1 me-5">
                <a class="{{request()->is('admin/invoice') ? 'nav-link nav-active' : 'nav-link' }}" aria-current="page" href="/admin/invoice">Paid Invoices</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/invoice/unpaid') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/invoice/unpaid">Unpaid Invoices</a>
            </li>
        </ul>

    </div>
</nav>

<div>
    @yield('child-content')
</div>

@endsection