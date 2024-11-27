@extends('layouts.email_template')

@section('content')
<div style="max-width: 600px; margin: 0 auto; padding: 30px 50px;">
    <table cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td>
                @php
                use Carbon\Carbon;
                $from=Carbon::now()->subDays(7)->toDateString();
                $to = Carbon::now()->toDateString();
                @endphp
                <div style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">Dear {{ $admin_name }},</p>

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;"><strong>Partners With Zero Clicks Data From {{$from}} To {{$to}}</strong></p>
                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">Here is the list</p>

                    <ul style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">

                        @foreach($partnerIdsWithNoClicks as $partner)

                        <li>Partner Name : {{$partner['partner_name']}}</li>

                        @endforeach

                    </ul>
                </div>
            </td>
        </tr>
    </table>
</div>
@endsection