@extends('layouts.email_template')

@section('content')
<div style="max-width: 600px; margin: 0 auto; padding: 30px 50px;">
    <table cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td>
                <div style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">Dear {{ $name }},</p>

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;"><strong>Company Info Details</strong></p>

                    <p style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">{{$partner_name}} have sent the Company Info Details.<br />Here are the details:</p>

                    <ul style="color: black; font-size: 16px; font-family: Verdana, sans-serif; font-weight: 400; text-align: left; line-height: 26px;">

                        <li>Company Name : {{$partner_name}}</li>

                        <li>Company Email : {{$partner_email}}</li>

                        <li>Company Company : {{$partner_company}}</li>

                        <li>Company Logo Uploaded : <a href="{{$logo_presigned_url}}"> logo_link</a></li>

                        <li>Company Landing Page Url : {{$landing_page_url}}</li>

                        <li>Company Tune Link : {{$tune_link}}</li>

                    </ul>
                </div>
            </td>
        </tr>
    </table>
</div>
@endsection