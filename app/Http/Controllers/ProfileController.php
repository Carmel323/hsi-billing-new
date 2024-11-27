<?php

namespace App\Http\Controllers;

use App\Mail\Email;
use App\Models\AccessToken;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Session;
use Exception;
use App\Models\Partner;
use App\Models\PartnerAddress;
use App\Models\PartnerUsers;
use App\Models\ProviderAvailabilityData;
use App\Models\ProviderData;
use App\Models\Subscriptions;
use App\Models\Support;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProfileController extends Controller
{

    //Get Profile
    public function getProfile(AccessToken $token)
    {
        if (Session::has('loginPartner')) {

            $token = AccessToken::latest('created_at')->first();

            $access_token = $token->access_token;

            $current_user = null;

            if (Session::has('loginId')) {
                $partner =  Partner::where('zoho_cust_id', '=', Session::get('loginId'))->first();

                $users = PartnerUsers::where('zoho_cust_id', '=', Session::get('loginId'))->get();

                $partner_address = PartnerAddress::where('zoho_cust_id', '=', Session::get('loginId'))->latest('created_at')->first();



                $paymentmethod = PaymentMethod::where('zoho_cust_id', Session::get('loginId'))->first();

                $subscription = Subscriptions::where('zoho_cust_id', '=', Session::get('loginId'))
                    ->where('status', 'live')->first();
                if (Session::has('userId')) {
                    $current_user = PartnerUsers::where('zoho_cpid', '=', Session::get('userId'))->first();

                    $users = PartnerUsers::where('zoho_cust_id', '=', Session::get('loginId'))->where('zoho_cpid', '!=', $current_user->zoho_cpid)->get();
                }
            }
            $showModal = false;
            $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();
            $availability_data = ProviderAvailabilityData::where('zoho_cust_id', Session::get('loginId'))->first();
            $company_info = ProviderData::where('zoho_cust_id', Session::get('loginId'))->first();

            // if ($availability_data === null || $company_info === null) {
            //     $showModal = true;
            // }
            return view('partner.profile', compact('partner', 'partner_address', 'paymentmethod', 'subscription', 'users', 'current_user', 'showModal', 'availability_data', 'company_info'));
        }
    }


    public function updateAddress(Request $request)
    {

        $support = Support::where('zoho_cust_id', Session::get('loginId'))
            ->where('request_type', 'Address')
            ->where('status', 'open')
            ->first();

        if ($support) {

            return back()->with('fail', 'Support ticket already raised');
        } else {

            $support = new Support();
            $support->date = date('d-M-Y');
            $support->request_type = 'Address';
            $support->message = "Update Address";
            $support->status = "open";
            $support->zoho_cust_id = Session::get('loginId');
            $support->zoho_cpid = Session::get('userId');
            $support->attributes = [
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'zip_code' => $request->zip_code,
            ];
            $support->save();

            return back()->with('success', 'Support ticket raised');
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'new_password' =>   ['required', Password::min(6)->letters()->numbers()->symbols()],
            ]);
        } catch (Exception $e) {
            return back()->with('fail', $e->getMessage());
        }

        $current_password = $request->current_password;

        $new_password = $request->new_password;

        $confirm_new_password = $request->confirm_new_password;

        $partner = PartnerUsers::where('zoho_cpid', Session::get('userId'))->first();

        if (Hash::check($current_password, $partner->password)) {

            if ($new_password === $confirm_new_password) {

                $partner->password = Hash::make($new_password);

                $partner->save();

                return back()->with('success', 'Password updated successfully');
            } else {
                return back()->with('fail', 'Password confirmation do not match');
            }
        } else {

            return back()->with('fail', 'Please enter the correct current password');
        }
    }

    public function inviteUser(Request $request, AccessToken $token)
    {
        $partner_url = env('PARTNER_URL');

        $user = PartnerUsers::where('email', $request->email)->first();


        if ($user) {

            return back()->with('fail', 'Email already exists');
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

            return back()->with('success', 'Invitation Email Sent Successfully');
        } catch (\Exception $e) {

            return back()->with('fail', 'Failed to send invitation: ' . $e->getMessage());
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
}
