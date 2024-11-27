@extends('layouts.email_template')

@section('content')
<div style="max-width: 600px; margin: 0 auto; padding: 30px 50px;">
    <table cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td>
                <div style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">   

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">Dear Admin,</p>

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">{{ $details['partner_name'] }}'s current plan is almost out of clicks, with {{ number_format($details['usage_percentage'], 2) }}% already used.</p>                    

                    @php
                    $monthNumber = $details['clicks_month'];
                    $monthName = DateTime::createFromFormat('!m', $monthNumber)->format('F');
                    @endphp

                    <table cellspacing="0" cellpadding="10" border="0" width="100%" style="margin-top: 20px; border-collapse: collapse;">
                        <tr>
                            <td><strong>Company Name :</strong></td>
                            <td>{{ $details['partner_name'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Subscribed Plan:</strong></td>
                            <td>{{ $details['subscribed_plan'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Addon:</strong></td>
                            <td>{{ $details['addon'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Clicks (Duration):</strong></td>
                            <td>{{ $monthName }} - {{ $details['clicks_year'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Plan Max Clicks:</strong></td>
                            <td>{{ $details['plan_max_clicks'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Addon Max Clicks:</strong></td>
                            <td>{{ $details['addon_max_clicks'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Max Allowed Clicks:</strong></td>
                            <td>{{ $details['max_allowed_clicks'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Clicks Count:</strong></td>
                            <td>{{ $details['clicks_count'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Usage Percentage:</strong></td>
                            <td>{{ number_format($details['usage_percentage'], 2) }}%</td>
                        </tr>
                    </table>

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">To avoid service interruption, consider either adding more clicks or upgrading the plan.</p>
                </div>
            </td>
        </tr>
    </table>
</div>
@endsection