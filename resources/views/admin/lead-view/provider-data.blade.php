@extends('layouts.view-lead-template')

@section('child-content')

<div style="width:80%" class="d-flex flex-row justify-content-between mt-5 mb-5">
    <div>
        <h5 class="fw-bold">Provider Data</h5>
    </div>
</div>

<div class="card partner-card border border-dark ms-0 m-2 mb-5">
    <div class="card-body table-responsive p-0">
        @if($data)
        <table class="text-center mt-1 m-0 table border-dark rounded">
            <thead>
                <tr>
                    <th class="fw-normal">Logo Image</th>
                    <th class="fw-normal">Landing Page Url</th>
                    <th class="fw-normal">Landing Page Url(Spanish)</th>
                    <th class="fw-normal">Company Name</th>
                </tr>
            </thead>

            <tbody>
                <tr class="py-3 text-center ">
                    <td class="text-primary"><img src="{{$url}}" alt="logo" style="width:125px;" /></td>
                    <td class="fw-bold">{{ isset($data) ? $data['landing_page_url'] : ''}}</td>
                    <td class="fw-bold">{{ isset($data) ? $data['landing_page_url_spanish']: ''}}</td>
                    <td class="fw-bold">{{ isset($data) ? $data['provider_company_name']: ''}}</td>
                </tr>
            </tbody>
        </table>
        @else
        <div class="d-flex justify-content-center align-items-center py-5">
            No Provider Data found
        </div>
        @endif
    </div>
</div>
<div style="width:80%" class="d-flex flex-row justify-content-between mt-5 mb-5">
    <div>
        <h5 class="fw-bold">Provider Availability Data</h5>
    </div>
</div>

<div class="card partner-card border border-dark ms-0 m-2 ">
    <div class="card-body table-responsive p-0">
        @if($availability_data)
        <table class="text-center mt-1 m-0 table border-dark  rounded">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>File Name</th>
                    <th>File Size</th>
                    <th>ZIP Count</th>
                    <th>CSV File URL</th>
                </tr>
            </thead>
            <tbody>

                <tr>
                    <td>{{ isset($availability_data) ? $lead->created_at : ''}}</td>
                    <td>{{ isset($availability_data) ? $availability_data['file_name'] : ''}}</td>
                    <td>{{ isset($availability_data) ? round($availability_data['file_size']/1024,2) : ''}}KB</td>
                    <td>{{ isset($availability_data) ? $availability_data['zip_count']: ''}}</td>
                    <td>
                        <button class="btn btn-primary btn-sm download-presigned-url" data-url="{{isset($availability_data) ?  $availability_data['url']: ''}}" data-token="{{ csrf_token() }}">Download</button>
                    </td>
                </tr>

            </tbody>
        </table>
        @else
        <div class="d-flex justify-content-center align-items-center py-5">
            No Provider Availability data found
        </div>
        @endif
    </div>
</div>
@endsection