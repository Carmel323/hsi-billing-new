@extends('layouts.email_template')

@section('content')
<div style="max-width: 600px; margin: 0 auto; padding: 30px 50px;">
    <table cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td>
                <div style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">Dear {{ $name }},</p>

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;"><strong>Subscription Cancellation Request</strong></p>

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">We have received a request to cancel your subscription.<br />Here are the details:</p>
                    <ul style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">
                        <li>Company Name: {{$company_name}}</li>
                        <li>Request By : {{$request_raised_by}}</li>
                        <li>Subscription Number : {{$subscription_number}}</li>
                        <li>Plan Name : {{$plan_name}}</li>
                        <li>Price :{{$plan_price}} USD </li>
                    </ul>
                </div>
            </td>
        </tr>
    </table>
</div>
@endsection