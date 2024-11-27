<?php

namespace App\Http\Controllers;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;
use Aws\Credentials\CredentialProvider;
use App\Mail\AdminCsvUpload;
use App\Mail\AdminSupport;
use App\Mail\CompanyInfoUpload;
use App\Models\Admin;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Models\Partner;
use App\Models\PartnerUsers;
use App\Models\ProviderData;
use App\Models\ProviderAvailabilityData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\Sandstone;


class ProviderDataController extends Controller
{
    private $s3Client;

    public function __construct()
    {
        $credentials = env('AWS_PROFILE') ? CredentialProvider::sso('profile ' . env('AWS_PROFILE')) : NULL;
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION'),
            'credentials' => $credentials
        ]);
    }

    public function getProviderAvailabilityData(Request $request)
    {
        $zohoCustId = Session::get('loginId');

        if (!$zohoCustId) {

            return redirect()->route('login')->with('error', 'You must be logged in to access this data.');
        }

        // $provider_data = ProviderData::where('zoho_cust_id', $zohoCustId)->first();

        $query = ProviderAvailabilityData::where('zoho_cust_id', $zohoCustId);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {

            $query->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        if ($request->has('search')) {

            $search = $request->input('search');

            $query->where(function ($q) use ($search) {

                $q->where('file_name', 'LIKE', "%{$search}%")
                    ->orWhere('zip_count', 'LIKE', "%{$search}%");
            });
        }

        $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

        $aoa_data = $query->orderByDesc('created_at')->paginate($perPage);

        $s3_domain_url = env('AWS_URL');

        $totalCount = DB::table('provider_availability_data')->where('zoho_cust_id', $zohoCustId)->count();

        $data = ProviderData::where('zoho_cust_id', $zohoCustId)->first();

        $url = null;

        $showModal = false;
        $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();
        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', Session::get('loginId'))->first();
        $company_info = ProviderData::where('zoho_cust_id', Session::get('loginId'))->first();

        // if ($availability_data === null || $company_info === null) {
        //     $showModal = true;
        // }
        return view('partner/provider-info', compact('aoa_data', 'totalCount', 's3_domain_url', 'showModal', 'availability_data', 'company_info'));
    }

    public function getProviderCompanyInfo()
    {
        $zohoCustId = Session::get('loginId');

        if (!$zohoCustId) {

            return redirect()->route('login')->with('error', 'You must be logged in to access this data.');
        }

        $provider_data = ProviderData::where('zoho_cust_id', $zohoCustId)->first();

        $admins = Admin::all();

        $url = null;

        if ($provider_data) {

            $url = $this->generatePresignedUrl($provider_data->logo_image);
        }

        $showModal = false;

        $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();

        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', Session::get('loginId'))->first();

        $company_info = ProviderData::where('zoho_cust_id', Session::get('loginId'))->first();


        // if ($availability_data === null || $company_info === null) {
        //     $showModal = true;
        // }


        return view('partner.company-info', compact('provider_data', 'url', 'showModal', 'availability_data', 'company_info', 'partner', 'admins'));
    }


    public function uploadAndProcessCSV(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();
        $user = PartnerUsers::where('zoho_cpid', Session::get('userId'))->first();

        if (!$partner) {
            return response()->json(['success' => false, 'message' => 'Partner record not found'], 404);
        }

        if ($request->hasFile('csv_file')) {
            $cleaned_data = [];

            $unique_rows = [];

            $csv_file = $request->file('csv_file');

            $timestamp = now()->format('YmdHis');

            try {
                $csv_content = file_get_contents($csv_file->path());
                $rows = explode("\n", $csv_content);

                if (count($rows) > 0) {

                    $header = str_getcsv($rows[0], ',');

                    $header = array_map(function ($col) {
                        return trim(str_replace("\xEF\xBB\xBF", '', $col));
                    }, $header);

                    if ($header[0] === 'ZIP' && $header[1] === 'Speed' && $header[2] === 'Type' && $header[3] === 'Coverage' && $header[4] === 'CustomerType') {

                        // Add header to cleaned data if it's valid
                        $cleaned_data[] = $header;
                    } else {


                        throw new \Exception('Invalid CSV header. Expected columns are ZIP, Speed, Type, Coverage, CustomerType.');
                    }
                }

                foreach ($rows as $index => $row) {
                    $data = str_getcsv($row, ',');

                    if ($index === 0) {
                        continue; // Skip the header row
                    }

                    if (count($data) === 5) {
                        $data[0] = str_pad($data[0], 5, '0', STR_PAD_LEFT);
                        $unique_key = $data[0] . '-' . $data[2] . '-' . $data[4];

                        if (!isset($unique_rows[$unique_key])) {
                            $unique_rows[$unique_key] = true;
                            $cleaned_data[] = $data;
                        }
                    }
                }

                $cleaned_csv_content = implode("\n", array_map(function ($row) {
                    return implode(',', $row);
                }, $cleaned_data));

                $cleaned_csv_filename = 'zip_list_template.csv';
                $cleaned_csv_path = $partner->zoho_cust_id . '/aoafile/' . $timestamp . '/';
                $csv_object_path = $cleaned_csv_path . $cleaned_csv_filename;

                Storage::disk('s3')->put($csv_object_path, $cleaned_csv_content);
                
                $result = $this->s3Client->headObject([
                    'Bucket' => env("AWS_BUCKET"),
                    'Key'    => $csv_object_path,
                ]);

                $fileSize = $result['ContentLength'];

                $providerAvailabilityData = new ProviderAvailabilityData();
                $providerAvailabilityData->file_size = $fileSize;
                $providerAvailabilityData->file_name = $cleaned_csv_filename;
                $providerAvailabilityData->zip_count = count($unique_rows);
                $providerAvailabilityData->url = $csv_object_path;
                $providerAvailabilityData->zoho_cust_id = $partner->zoho_cust_id;
                $providerAvailabilityData->uploaded_by = $user->first_name . ' ' . $user->last_name . '(partner)';
                $providerAvailabilityData->save();
                
                // Send notification to Sandstone that there is a new AOA file uploaded
                $sandstone = new Sandstone();
                $sandstone->AOAFileUploadNotification($partner->company_name, $providerAvailabilityData->id, $this->generatePresignedUrl($csv_object_path));

                $company_info = ProviderData::where('zoho_cust_id', Session::get('loginId'))->first();

                if ($company_info) {

                    $this->sendMailToAdmin($providerAvailabilityData, $company_info);
                }

                Storage::disk('local')->delete($csv_file->path());

                return redirect('/provider-info')->with('success', 'CSV data processed and cleaned successfully ');
            } catch (\Exception $e) {
                Log::error('Error processing CSV: ' . $e->getMessage());
                return redirect()->back()->withErrors(['csv_file' => 'Error processing CSV: ' . $e->getMessage()])->withInput();
            }
        }

        return redirect()->back()->withErrors(['csv_file' => 'Please upload a valid CSV file.'])->withInput();
    }


    public function sendMailToAdmin($data, $company_info)
    {
        $admins = Admin::where('receive_mails', 'Yes')->get();

        $partner = Partner::where('zoho_cust_id', $data->zoho_cust_id)->first();

        $current_partner_user = PartnerUsers::where('zoho_cpid', Session::get('userId'))->first();


        $partner_name = $current_partner_user->first_name . ' ' . $current_partner_user->last_name;

        $partner_company = $partner->company_name;

        $partner_email = $current_partner_user->email;

        $app_url = env('APP_URL');

        $file_url = $data->url;

        $file_name = $data->file_name;

        $presigned_url = $this->generatePresignedUrl($file_url);

        $url = $company_info->logo_image;

        $logo_presigned_url = $this->generatePresignedUrl($url);

        $landing_page_url = $company_info->landing_page_url;

        $tune_link = $company_info->tune_link;

        foreach ($admins as $admin) {

            $name = $admin->admin_name;

            Mail::to(users: $admin->email)->send(new AdminCsvUpload($file_url, $partner_name, $partner_email, $partner_company, $name, $file_name, $presigned_url, $logo_presigned_url, $landing_page_url, $url, $tune_link));
        }
    }

    public function downloadPresignedUrl(Request $request)
    {
        try {
            $cmd = $this->s3Client->getCommand('GetObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key'    => $request->input('url'),
            ]);
            
            $presignedUrl = (string) $this->s3Client->createPresignedRequest($cmd, '+20 minutes')->getUri();
            return response()->json(['url' => $presignedUrl], 200);
        
        } catch (\Exception $e) {
            Log::error('Error generating presigned URL: ' . $e->getMessage());
            return response()->json(['message' => 'Error generating presigned URL.'], 500);
        }
    }


    public function downloadPresignedLogo(Request $request)
    {
        $url = $request->input('url');
        try {
            $result = $this->s3Client->getObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key'    => $url
            ]);

            $tempFile = tempnam(sys_get_temp_dir(), 'download');

            file_put_contents($tempFile, $result['Body']);

            return response()->download($tempFile, basename($url), [
                'Content-Type' => $result['ContentType'],
            ])->deleteFileAfterSend(true);
        
        } catch (\Exception $e) {
            Log::error('Error downloading file: ' . $e->getMessage());
            return response()->json(['message' => 'Error downloading file.'], 500);
        }
    }



    public function saveProviderData(Request $request)
    {
        $request->validate([
            'logo' => 'required|mimes:png,jpg,svg',
            'landing_page_url' => 'required|url',

        ]);

        $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();
        $user = PartnerUsers::where('zoho_cpid', Session::get('userId'))->first();


        if ($request->has('logo')) {
            $file = $request->file('logo');
            $partner_company_name = $partner->company_name;
            $formatted_company_name = str_replace(' ', '_', $partner_company_name);
            $formatted_company_name = strtolower($formatted_company_name);
            $filename = $formatted_company_name;
            $extension = $file->getClientOriginalExtension();
            $filename .= '.' . $extension;
            $timestamp = now()->format('YmdHis');
            $path =  $partner->zoho_cust_id . '/partner-logo/' . $timestamp . '/';
            $logo_object_path = $path . $filename;
            Storage::disk('s3')->put($logo_object_path, file_get_contents($file));
        }


        $data = new ProviderData();
        $data->logo_image = $path . $filename;
        $data->landing_page_url = $request->landing_page_url;
        $data->landing_page_url_spanish = $request->landing_page_url_spanish;
        $data->company_name = $request->company_name;
        $data->business_sales_phone_number = $request->business_sales_phone_number;
        $data->residential_sales_phone_number = $request->residential_sales_phone_number;
        $data->zoho_cust_id = $partner->zoho_cust_id;
        $data->uploaded_by = $user->first_name . ' ' . $user->last_name . '(partner)';
        $data->tune_link = 'NA';
        $data->save();

        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', Session::get('loginId'))->first();

        if ($availability_data) {
            $this->sendMailToAdmin($availability_data, $data);
        }


        return redirect('/company-info')->with('success', 'Provider Data Uploaded Successfully');
    }

    public function updateProviderData(Request $request)
    {
        $request->validate([
            'logo' => 'mimes:png,jpg,svg',
            'landing_page_url' => 'required|url',

        ]);

        $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();
        $data = ProviderData::where('zoho_cust_id', Session::get('loginId'))->first();


        if ($request->has('logo')) {
            $file = $request->file('logo');
            $partner_company_name = $partner->company_name;
            $formatted_company_name = str_replace(' ', '_', $partner_company_name);
            $formatted_company_name = strtolower($formatted_company_name);
            $filename = $formatted_company_name;
            $extension = $file->getClientOriginalExtension();
            $filename .= '.' . $extension;
            $timestamp = now()->format('YmdHis');
            $path =  $partner->zoho_cust_id . '/partner-logo/' . $timestamp . '/';
            $logo_object_path = $path . $filename;
            Storage::disk('s3')->put($logo_object_path, file_get_contents($file));
            $data->logo_image = $path . $filename;
        }
        $data->landing_page_url = $request->landing_page_url;
        $data->landing_page_url_spanish = $request->landing_page_url_spanish;
        $data->company_name = $request->company_name;
        $data->save();


        return redirect('/company-info')->with('success', 'Provider Data Uploaded Successfully');
    }


    public function generatePresignedUrl($objectKey)
    {
        try {
            $command = $this->s3Client->getCommand('GetObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $objectKey,
            ]); 
            
            try {
                return (string) $this->s3Client->createPresignedRequest($command, '+20 minutes')->getUri();
            } catch (\Exception $e) {
                Log::error('Error generating presigned URL: ' . $e->getMessage());
            }
        } catch (AwsException $e) {
            Log::error('Error generating presigned URL: ' . $e->getMessage());
        }
        return;
    }

    public function downloadS3Files($filepath)
    {

        if (!Storage::disk('s3')->exists($filepath)) {
            return abort(404, 'File not found.');
        }
        $presignedUrl = $this->generatePresignedUrl($filepath);

        if (!$presignedUrl) {

            return abort(500, 'Error generating presigned URL.');
        }

        $response = Http::get($presignedUrl);

        return response($response->body(), 200)
            ->header('Content-Type', $response->header('Content-Type'))
            ->header('Content-Disposition', 'attachment; filename="' . basename($filepath) . '"');
    }

    public function sendDetailToAdmin(Request $request)
    {
        $company_info = ProviderData::where('zoho_cust_id', $request->partner_id)->first();
        $partner = Partner::where('zoho_cust_id', $request->partner_id)->first();
        $current_partner_user = PartnerUsers::where('zoho_cpid', Session::get('userId'))->first();
        $admin = Admin::find($request->admin_id);
        $partner_name = $current_partner_user->first_name . ' ' . $current_partner_user->last_name;
        $partner_company = $partner->company_name;
        $partner_email = $current_partner_user->email;
        $name = $admin->admin_name;
        $url = $company_info->logo_image;
        $logo_presigned_url = $this->generatePresignedUrl($url);
        $landing_page_url = $company_info->landing_page_url;
        $tune_link = $company_info->tune_link;
        Mail::to(users: $admin->email)->send(new CompanyInfoUpload($partner_name, $partner_email, $partner_company, $name, $logo_presigned_url, $landing_page_url, $url, $tune_link));
        return back()->with('success', 'Details sent successfully');
    }
}
