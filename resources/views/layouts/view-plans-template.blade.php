@extends('layouts.admin_template')

@section('content')
<div class="d-flex mt-4 flex-row justify-content-between ">
    <h2 class="">Plans</h2>
    <a href="/sync-plans" class="btn button-clearlink text-primary fw-bold">Sync Plans With Zoho Billing</a>
</div>

<nav class="navbar1 navbar-expand-lg border-bottom border-dark">
    <div class="container-fluid">

        <ul class="navbar-nav">
            <li class="nav-item m-1 me-5">
                <a class="{{request()->is('admin') ? 'nav-link nav-active' : 'nav-link' }}" aria-current="page" href="/admin">Flat Plans</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/cpc-plans') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/cpc-plans">CPC Plans</a>
            </li>
        </ul>

    </div>
</nav>

<div>
    @yield('child-content')
</div>

@endsection