@extends('layouts.email_template')

@section('content')
<div style="max-width: 600px; margin: 0 auto; padding: 30px 50px;">
    <table cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td>
                <div style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">Dear {{ $name }},</p>

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;"><strong>New AOA File Upload</strong></p>

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">{{$partner_name}} have uploaded a new AOA File.<br />Here are the details:</p>

                    <ul style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">

                        <li>Provider Name : {{$partner_name}}</li>

                        <li>Provider Email : {{$partner_email}}</li>

                        <li>Provider Company : {{$partner_company}}</li>

                        @if($file_name)

                        <li>Provider AOA CSV File (Link Expires) : <a href="{{$presigned_url}}">{{$file_name}}</a></li>
                        @else

                        <li>Provider AOA CSV File (Link Expires) : not yet uploaded</li>
                        @endif
                        <li>Provider Logo Uploaded : <a href="{{$logo_presigned_url}}"> logo_link</a></li>

                        <li>Provider Landing Page Url : {{$landing_page_url}}</li>

                        <li>Provider Tune Link : {{$tune_link}}</li>

                    </ul>
                </div>
            </td>
        </tr>
    </table>
</div>
@endsection