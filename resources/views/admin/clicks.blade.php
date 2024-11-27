@extends('layouts.admin_template')

@section('content')
<div class="inner">
    <h3>Clicks Data</h3>
    <style>
        .clicks-table {
            font-size: small;
        }
    </style>
    ---------------------------------------------

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Partners Affiliates ID</th>
                <th>Partner ID</th>
                <th>Affiliate ID</th>
                <th>Partner Company Name</th>
                <th>Domain Name</th>
                <th>Clicks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($partnerCounts as $partnerCount)
            <tr>
                <td>{{ $partnerCount->id }}</td>
                <td>{{ $partnerCount->partner_id }}</td>
                <td>{{ $partnerCount->affiliate_id }}</td>
                <td>{{ $partnerCount->company_name }}</td>
                <td>{{ $partnerCount->domain_name }}</td>
                <td>{{ $partnerCount->count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    -----------------------------


    <div class="table-responsive">
        <table class="table table-bordered clicks-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Click Timestamp</th>
                    <th>Click ID</th>
                    <th>Click Source</th>
                    <th>Affiliate Source URL</th>
                    <th>Partner Affiliate ID</th>
                    <th>Zip</th>
                    <th>State</th>
                    <th>City</th>
                    <th>Intended Zip</th>
                    <th>Intended State</th>
                    <th>Intended City</th>
                    <th>Channel</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clicks as $click)
                <tr>
                    <td>{{ $click->id }}</td>
                    <td>{{ $click->click_ts }}</td>
                    <td>{{ $click->click_id }}</td>
                    <td>{{ $click->click_source }}</td>
                    <td>{{ $click->affiliate_source_url }}</td>
                    <td>{{ $click->partners_affiliates_id }}</td>
                    <td>{{ $click->zip }}</td>
                    <td>{{ $click->state }}</td>
                    <td>{{ $click->city }}</td>
                    <td>{{ $click->intended_zip }}</td>
                    <td>{{ $click->intended_state }}</td>
                    <td>{{ $click->intended_city }}</td>
                    <td>{{ $click->channel }}</td>
                    <td>{{ $click->created_at }}</td>
                    <td>{{ $click->updated_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $clicks->links() }}

    </div>
</div>
@endsection