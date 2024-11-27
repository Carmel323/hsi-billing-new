@extends('layouts.admin_template')

@section('content')
<div class="d-flex flex-row ">
    <h5 class="fw-bold mt-1">{{$partner->company_name}}</h5>
    @if($partner->status ==='active')
    <span class="badge-success p-1 status ms-3 mb-2">{{ $partner->status }}</span>
    @elseif($partner->status ==='inactive')
    <span class="badge-fail p-1 status ms-3 mb-2">{{ $partner->status }}</span>
    @elseif($partner->status === 'Invited')
    <span class="badge-revoked p-1 status ms-3 mb-2">{{ $partner->status }}</span>
    @endif
</div>

<nav class="navbar1 navbar-expand-lg border-bottom border-dark">
    <div class="container-fluid">

        <ul class="navbar-nav">
            <li class="nav-item m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id) ? 'nav-link nav-active' : 'nav-link' }}" aria-current="page" href="/admin/view-partner/{{$partner->id}}">Overview</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/subscriptions') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/subscriptions">Subscriptions</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/invoices') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/invoices">Invoices</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/creditnotes') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/creditnotes">Credit Notes</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/refunds') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/refunds">Refunds</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/provider-data') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/provider-data">Provider Data</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/clicks-data') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/clicks-data">Clicks Data</a>
            </li>
            <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-partner/'.$partner->id. '/selected-plans') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-partner/{{$partner->id}}/selected-plans">Select Plans</a>
            </li>
        </ul>

    </div>
</nav>

<div>
    @yield('child-content')
</div>

@endsection