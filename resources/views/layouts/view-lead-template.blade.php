@extends('layouts.admin_template')

@section('content')
<div class="d-flex flex-row ">
    <h5 class="fw-bold mt-1">{{$lead->company_name}}</h5>
</div>

<nav class="navbar1 navbar-expand-lg border-bottom border-dark">
    <div class="container-fluid">

        <ul class="navbar-nav">
            <li class="nav-item m-1 me-5">
                <a class="{{request()->is('admin/view-lead/'.$lead->id) ? 'nav-link nav-active' : 'nav-link' }}" aria-current="page" href="/admin/view-lead/{{$lead->id}}">Overview</a>
            </li>
            <!-- <li class="nav-item  m-1 me-5">
                <a class="{{request()->is('admin/view-lead/'.$lead->id. '/provider-data') ? 'nav-link nav-active' : 'nav-link' }}" href="/admin/view-lead/{{$lead->id}}/provider-data">Provider Data</a>
            </li> -->
        </ul>

    </div>
</nav>

<div>
    @yield('child-content')
</div>

@endsection