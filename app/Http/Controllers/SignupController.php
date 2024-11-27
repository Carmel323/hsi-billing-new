<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Mail\Signup;
use App\Models\Admin;
use App\Models\Leads;
use App\Models\OtpPartnerUser;
use App\Models\Partner;
use App\Models\PartnerUsers;
use App\Models\ProviderAvailabilityData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class SignupController extends Controller
{
    public function signupPartner(Request $request)
    {
        $request->validate([
            'company_name' => 'required',
            'tax_number' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
        ]);

        $existing_partner = PartnerUsers::where('email', $request->email)->first();
        if ($existing_partner) {
            return back()->with('fail', 'Email already exists');
        }

        $this->sendOtpAndRedirect($request);

        return redirect('/verify-otp-signup')->with('success', 'A one-time password (OTP) verification code has been sent to your email. Please check your email and enter the code.');
    }

    private function sendOtpAndRedirect($request)
    {
        $otp = Str::random(6);

        $expiresAt = Carbon::now()->addMinutes(10);

        $otpEntry = OtpPartnerUser::where('email', $request->email)->first();

        if (!$otpEntry) {
            $otpEntry = new OtpPartnerUser();
            $otpEntry->email = $request->email;
        }
        $otpEntry->lead_data = [
            'company_name' => $request->company_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'tax_number' => $request->tax_number,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'street' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip_code' => $request->zip_code,
        ];
        $otpEntry->otp = $otp;
        $otpEntry->expires_at = $expiresAt;
        $otpEntry->save();

        Mail::to($request->email)->send(new OtpMail($otp));

        session()->put('email', $request->email);
    }

    public function verifySignupOtpForm()
    {
        return view('partner.verify-otp-signup');
    }

    public function verifySignupOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $otp_entry = OtpPartnerUser::where('email', $request->email)->where('otp', $request->otp)->first();

        if ($otp_entry && Carbon::now()->lessThanOrEqualTo($otp_entry->expires_at)) {
            $lead = new Leads();
            $lead->company_name = $otp_entry->lead_data['company_name'];
            $lead->first_name = $otp_entry->lead_data['first_name'];
            $lead->last_name = $otp_entry->lead_data['last_name'];
            $lead->tax_number = $otp_entry->lead_data['tax_number'];
            $lead->email = $otp_entry->lead_data['email'];
            $lead->phone_number = $otp_entry->lead_data['phone_number'];
            $lead->street = $otp_entry->lead_data['street'];
            $lead->city = $otp_entry->lead_data['city'];
            $lead->state = $otp_entry->lead_data['state'];
            $lead->zip_code = $otp_entry->lead_data['zip_code'];
            $lead->save();

            $this->sendMailToAdmin($lead);
            return redirect('/register-success');
        } else {

            return back()->with('fail', 'Invalid or expired OTP');
        }
    }

    public function resendSignupOtp(Request $request)
    {
        $email = $request->session()->get('email');

        if ($email) {
            $otp = Str::random(6);
            $expiresAt = Carbon::now()->addMinutes(10);

            $otpEntry = OtpPartnerUser::where('email', $email)->first();

            if (!$otpEntry) {
                $otpEntry = new OtpPartnerUser();
                $otpEntry->email = $email;
            }
            $otpEntry->otp = $otp;

            $otpEntry->expires_at = $expiresAt;

            $otpEntry->save();

            Mail::to($email)->send(new OtpMail($otp));

            return redirect()->back()->with('success', 'OTP has been resent successfully.');
        } else {
            return redirect()->back()->with('fail', 'Email address not found. Please log in again.');
        }
    }

    public function sendMailToAdmin($lead)
    {
        $admins = Admin::where('receive_mails', 'Yes')->get();
        $partner_company = $lead->company_name;

        $parnter_username = $lead->first_name . ' ' . $lead->last_name;

        $partner_email = $lead->email;

        $partner_ph_number = $lead->phone_number;

        // $file_url = $lead->availability_data['url'];

        // $file_name = $lead->availability_data['file_name'];

        // $presigned_url = $this->generatePresignedUrl($file_url);

        // $file_name = null;

        // $presigned_url = null;



        foreach ($admins as $admin) {
            $name = $admin->admin_name;
            Mail::to(users: $admin->email)->send(new Signup($partner_company, $parnter_username, $partner_email, $partner_ph_number, $name));
        }
    }

    // public function generatePresignedUrl($objectKey)
    // {
    //     try {
    //         $s3Client = new S3Client([
    //             'version' => 'latest',
    //             'region' => env('AWS_DEFAULT_REGION'),
    //         ]);

    //         $command = $s3Client->getCommand('GetObject', [
    //             'Bucket' => env('AWS_BUCKET'),
    //             'Key' => $objectKey,
    //         ]);

    //         $presignedUrl = (string) $s3Client->createPresignedRequest($command, '+6 days')->getUri();

    //         return $presignedUrl;
    //     } catch (AwsException $e) {
    //         \Log::error('Error generating presigned URL: ' . $e->getMessage());
    //         return null;
    //     }
    // }


    // public function registerLead(Request $request)
    // {

    //     if ($request->hasFile('csv_file')) {

    //         $csv_file = $request->file('csv_file');
    //         $timestamp = now()->format('YmdHis');


    //         $csv_content = file_get_contents($csv_file->path());
    //         $rows = explode("\n", $csv_content);

    //         $cleaned_data = [];
    //         $unique_rows = [];

    //         foreach ($rows as $index => $row) {
    //             if ($index === 0) {
    //                 continue; // Skip header row
    //             }

    //             $data = str_getcsv($row, ',');

    //             if (count($data) === 5) {
    //                 // Format ZIP code to 5 digits
    //                 $data[0] = str_pad($data[0], 5, '0', STR_PAD_LEFT);

    //                 $unique_key = $data[0] . '-' . $data[2] . '-' . $data[4];

    //                 if (!isset($unique_rows[$unique_key])) {
    //                     $unique_rows[$unique_key] = true;
    //                     $cleaned_data[] = $data;
    //                 }
    //             }
    //         }

    //         $cleaned_csv_content = implode("\n", array_map(function ($row) {
    //             return implode(',', $row);
    //         }, $cleaned_data));


    //         $cleaned_csv_filename = 'zip_list_template.csv';

    //         $cleaned_csv_path = 'partner-aoa/cleaned/';
    //         $csv_object_path = $cleaned_csv_path . $cleaned_csv_filename;

    //         Storage::disk('s3')->put($csv_object_path, $cleaned_csv_content);

    //         $client = new S3Client([
    //             'version' => 'latest',
    //             'region'  => env('AWS_DEFAULT_REGION'),
    //         ]);

    //         $bucket = env('AWS_BUCKET');

    //         $result = $client->headObject([
    //             'Bucket' => $bucket,
    //             'Key'    => $csv_object_path,
    //         ]);

    //         $fileSize = $result['ContentLength'];

    //         Storage::disk('local')->delete($csv_file->path());
    //     }


    //     if ($request->has('logo')) {
    //         $file = $request->file('logo');
    //         $filename = $file->getClientOriginalName();
    //         $path = 'partner-logo/logo-image/';
    //         $logo_object_path = $path . $filename;
    //         Storage::disk('s3')->put($logo_object_path, file_get_contents($file));
    //     }

    //     $otp_entry = OtpPartnerUser::where('email', $request->email)->first();
    //     // $fileSize = null;
    //     // $cleaned_csv_filename = null;
    //     // $unique_rows = [1, 2, 3];
    //     // $csv_object_path = null;

    //     $lead_data = $otp_entry->lead_data;
    //     $lead_data['file_size'] = $fileSize;
    //     $lead_data['file_name'] = $cleaned_csv_filename;
    //     $lead_data['zip_count'] = count($unique_rows);
    //     $lead_data['url'] = $csv_object_path;
    //     $lead_data['logo_image'] = $path . $filename;
    //     $lead_data['landing_page_url'] = $request->landing_page_url;
    //     $lead_data['landing_page_url_spanish'] = $request->landing_page_url_spanish;
    //     $lead_data['provider_company_name'] = $request->company_name;

    //     $otp_entry->lead_data = $lead_data;

    //     $otp_entry->save();

    //     $lead = new Leads();
    //     $lead->company_name = $otp_entry->lead_data['company_name'];
    //     $lead->first_name = $otp_entry->lead_data['first_name'];
    //     $lead->last_name = $otp_entry->lead_data['last_name'];
    //     $lead->tax_number = $otp_entry->lead_data['tax_number'];
    //     $lead->email = $otp_entry->lead_data['email'];
    //     $lead->phone_number = $otp_entry->lead_data['phone_number'];
    //     $lead->street = $otp_entry->lead_data['street'];
    //     $lead->city = $otp_entry->lead_data['city'];
    //     $lead->state = $otp_entry->lead_data['state'];
    //     $lead->zip_code = $otp_entry->lead_data['zip_code'];
    //     $lead->availability_data = [
    //         'file_size' => $otp_entry->lead_data['file_size'],
    //         'file_name' => $otp_entry->lead_data['file_name'],
    //         'zip_count' => $otp_entry->lead_data['zip_count'],
    //         'url' => $otp_entry->lead_data['url'],
    //     ];
    //     $lead->company_info = [
    //         'logo_image' => $otp_entry->lead_data['logo_image'],
    //         'landing_page_url' => $otp_entry->lead_data['landing_page_url'],
    //         'landing_page_url_spanish' => $otp_entry->lead_data['landing_page_url_spanish'],
    //         'provider_company_name' => $otp_entry->lead_data['provider_company_name'],
    //     ];
    //     $lead->save();

    //     OtpPartnerUser::where('email', $request->email)->delete();

    //     $this->sendMailToAdmin($lead);

    //     return redirect('/register-success');
    // }

    public function registerSuccess()
    {
        return view('partner.signup-successful');
    }
}
