<?php

namespace App\Http\Controllers;

use App\Models\AccessToken;
use App\Models\User;
use App\Models\Users;
use Illuminate\Http\Request;
use App\Mail\Email;
use App\Mail\OtpMail;
use App\Mail\ResetPassword;
use App\Models\OtpPartnerUser;
use App\Models\Partner;
use App\Models\PartnerUsers;
use App\Models\PasswordTokenPartnerUser;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PartnerUsersController extends Controller
{
    public function inviteUser(Request $request, $id, AccessToken $token)
    {
        $partner_url = env('PARTNER_URL');

        $user = PartnerUsers::where('email', $request->email)->first();


        if ($user) {

            return redirect('/admin/view-partner/' . $id . '')->with('fail', 'Email already exists');
        }

        try {

            $token1 = AccessToken::latest('created_at')->first();
            if ($token1 !== null) {
                $access_token = $token1->access_token;
            } else {
                $token->getToken();
                $token1 = AccessToken::latest('created_at')->first();
                return back()->with('fail', 'Kindly Try Again');
            }

            $client = new \GuzzleHttp\Client();

            $options = [
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    "first_name" => $request->first_name,
                    "last_name" => $request->last_name,
                    "email" => $request->email,
                    "mobile" => $request->phone_number,
                ]
            ];

            $res = $client->request(
                'POST',
                $partner_url . '/' . $request->zoho_cust_id . '/contactpersons',
                $options
            );

            $response = json_decode($res->getBody()->getContents());

            $unhashedPassword = Str::random(16);

            $newUser = new PartnerUsers();

            $newUser->first_name = $response->contactperson->first_name;
            $newUser->last_name = $response->contactperson->last_name;
            $newUser->email = $response->contactperson->email;
            $newUser->phone_number = $response->contactperson->mobile;
            $newUser->zoho_cust_id = $request->zoho_cust_id;
            $newUser->zoho_cpid = $response->contactperson->contactperson_id;
            $newUser->password = Hash::make($unhashedPassword);
            $newUser->invitation_status = "Invited";
            $newUser->status = "active";
            $newUser->is_primary = false;
            $newUser->save();

            $partner = Partner::where('zoho_cust_id', $request->zoho_cust_id)->first();

            $this->sendEmail($newUser->first_name, $newUser->email, $unhashedPassword, $partner->company_name);

            return redirect('/admin/view-partner/' . $id . '')->with('success', 'Invitation Email Sent Successfully');
        } catch (\Exception $e) {

            return redirect('/admin/view-partner/' . $id . '')->with('fail', 'Failed to send invitation: ' . $e->getMessage());
        }
    }


    public function sendEmail($name, $email, $password, $company_name)
    {
        $app_url = env('APP_URL');

        try {
            Mail::to($email)->send(new Email($app_url, $name, $password,  $company_name));
            return "Mail send!";
        } catch (\Exception $e) {



            return redirect('/admin/partner')->with('fail', $e->getMessage());
        }
    }

    public function updatePartnerUser(Request $request, AccessToken $token)
    {

        $partner_url = env('PARTNER_URL');


        $token1 = AccessToken::latest('created_at')->first();
        if ($token1 !== null) {
            $access_token = $token1->access_token;
        } else {
            $token->getToken();
            $token1 = AccessToken::latest('created_at')->first();
        }

        $client = new \GuzzleHttp\Client();


        $options = [
            'headers' => [
                'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
                'content-type: application/json'
            ],
            'json' => [
                "first_name" => $request->first_name,
                "last_name" => $request->last_name,
                "email" => $request->email,
                "mobile" => $request->phone_number,
            ]
        ];

        $partner_user_update_url = $partner_url . '/' . $request->zoho_cust_id . '/contactpersons/' . $request->zoho_cpid;

        $res = $client->request(
            'PUT',
            $partner_user_update_url,
            $options
        );

        $response = (string) $res->getBody();
        $response = json_decode($response);
        $contact = $response->contactperson;
        $user = PartnerUsers::where('zoho_cpid', $request->zoho_cpid)->first();
        $user->first_name = $contact->first_name;
        $user->last_name = $contact->last_name;
        $user->email = $contact->email;
        $user->phone_number = $contact->mobile;
        $user->save();

        return back()->with('success', 'User Updated Successfully');
    }

    public function disablePartnerUser()
    {
        $id = Route::getCurrentRoute()->id;
        $user = PartnerUsers::where('zoho_cpid', $id)->first();
        $user->status = 'inactive';
        $user->save();
        return back()->with('success', 'User Marked as Inactive Successfully');
    }

    public function reactivatePartnerUser()
    {
        $id = Route::getCurrentRoute()->id;
        $user = PartnerUsers::where('zoho_cpid', $id)->first();
        $user->status = 'active';
        $user->save();
        return back()->with('success', 'User Marked as Active Successfully');
    }

    public function markAsPrimary(AccessToken $token)
    {
        $id = Route::getCurrentRoute()->id;
        $user = PartnerUsers::where('zoho_cpid', $id)->first();
        $primary_user = PartnerUsers::where('zoho_cust_id', $user->zoho_cust_id)->where('is_primary', true)->first();
        $primary_contact_url = env('PRIMARY_CONTACT_URL');

        try {

            $token1 = AccessToken::latest('created_at')->first();
            if ($token1 !== null) {
                $access_token = $token1->access_token;
            } else {
                $token->getToken();
                $token1 = AccessToken::latest('created_at')->first();
                return back()->with('fail', 'Kindly Try Again');
            }

            $client = new \GuzzleHttp\Client();

            $options = [
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                    'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID')
                ]

            ];

            $primary_url = $primary_contact_url . $id . '/primary';

            $res = $client->request(
                'POST',
                $primary_url,
                $options
            );

            $response = json_decode($res->getBody()->getContents());

            $user->is_primary = true;
            $user->save();
            $primary_user->is_primary  = false;
            $primary_user->save();

            return back()->with('success', 'User marked as primary successfully');
        } catch (\Exception $e) {

            return back()->with('fail', 'Failed  ' . $e->getMessage());
        }
    }

    public function resetView()
    {
        $showModal = false;
        return view('partner.reset-password-partner-user', compact('showModal'));
    }

    public function resetMail()
    {
        $showModal = false;
        return view('partner.reset-mail', compact('showModal'));
    }
}
