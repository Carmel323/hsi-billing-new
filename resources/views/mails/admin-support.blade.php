@extends('layouts.email_template')

@section('content')
<div style="max-width: 600px; margin: 0 auto; padding: 30px 50px;">
    <table cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td>
                <div style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">Dear {{ $name }},</p>

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;"><strong>New Support Request</strong></p>

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">You have received a new support request from <strong>{{$company_name}}</strong>.<br />Here are the details:</p>
                    <ul style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">
                        <li>Email-ID : {{$email}}</li>
                        <li>Request_type : {{$request_type}}</li>
                        @if($subscription_number)
                        <li>Subscription Number : {{$subscription_number}}</li>
                        @endif
                        <li>Message : {{$req_message}}</li>
                    </ul>
                </div>
            </td>
        </tr>
    </table>
</div>
@endsection