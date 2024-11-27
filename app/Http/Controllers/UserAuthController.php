<?php

namespace App\Http\Controllers;

use App\Mail\AdminResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

use App\Mail\ResetPassword;
use App\Mail\OtpMail;
use App\Models\Partner;
use App\Models\Admin;
use App\Models\PartnerAddress;
use App\Models\PasswordToken;
use App\Models\Support;
use App\Models\Otp;
use App\Models\OtpPartner;
use App\Models\OtpPartnerUser;
use App\Models\PartnerUsers;
use App\Models\PasswordTokenAdmin;
use App\Models\PasswordTokenPartner;
use App\Models\PasswordTokenPartnerUser;
use App\Models\ProviderAvailabilityData;
use App\Models\ProviderData;
use App\Models\Subscriptions;
use App\Models\Users;

class UserAuthController extends Controller
{

    public function login()
    {
        return view('partner.login');
    }

    public function signup()
    {
        return view('partner.signup');
    }

    public function adminLogin()
    {
        return view('admin.admin-login');
    }

    public function incorrectPartnerUser()
    {
        return view('partner.incorrect-partner');
    }

    public function incorrectAdminUser()
    {
        return view('admin.incorrect-admin');
    }
    public function incorrectSuperAdminUser()
    {
        return view('admin.incorrect-superadmin');
    }



    public function loginUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $partnerUser = PartnerUsers::where('email', $request->email)->first();

        if ($partnerUser) {

            if (Hash::check($request->password, $partnerUser->password)) {

                $partner = Partner::where('zoho_cust_id', $partnerUser->zoho_cust_id)->where('status', 'active')->first();

                if ($partner) {

                    if ($partner->status === 'active') {

                        if ($partnerUser->status === 'active') {

                            $this->sendOtpAndRedirect($partnerUser->email);

                            return redirect('/verify-otp')->with('success', 'A one-time password (OTP) verification code has been sent to your email. Please check your email and enter the code.');
                        } else {

                            return redirect('/login')->with('fail', 'User account is inactive');
                        }
                    } else {

                        return redirect('/login')->with('fail', 'Company Account is inactive');
                    }
                } else {

                    $token = new PasswordTokenPartnerUser();
                    $token->email = $partnerUser->email;
                    $token->password_token = Str::random(100);
                    $token->save();

                    return redirect('/change-password/' . $token->password_token);
                }
            } else {

                return redirect('/login')->with('fail', 'Invalid Password');
            }
        }

        return back()->with('fail', 'This email is not registered');
    }

    private function sendOtpAndRedirect($email)
    {
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

        session()->put('email', $email);
    }


    public function verifyOtpForm()
    {
        return view('partner.verify-otp');
    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $otpEntry = OtpPartnerUser::where('email', $request->email)->where('otp', $request->otp)->first();

        if ($otpEntry && Carbon::now()->lessThanOrEqualTo($otpEntry->expires_at)) {

            $user = PartnerUsers::where('email', $request->email)->first();
            $provider_data = ProviderData::where('zoho_cust_id', $user->zoho_cust_id)->first();
            $availability_data = ProviderAvailabilityData::where('zoho_cust_id', $user->zoho_cust_id)->first();
            $subscription =  Subscriptions::where('zoho_cust_id', $user->zoho_cust_id)->first();

            session()->put('loginId', $user->zoho_cust_id);
            session()->put('loginPartner', $user->email);
            session()->put('userId', $user->zoho_cpid);

            OtpPartnerUser::where('email', $user->email)->delete();

            if ($availability_data === null || $provider_data === null) {
                return redirect('/provider-info')->with('success', 'Logged in successfully');
            } else {
                if ($subscription) {
                    return redirect('/clicks-report')->with('success', 'Logged in successfully');
                } else {
                    return redirect('/')->with('success', 'Logged in successfully');
                }
            }
        } else {

            return back()->with('fail', 'Invalid or expired OTP');
        }
    }


    public function resendOtp(Request $request)
    {
        $email = $request->session()->get('email');

        if ($email) {

            $user = PartnerUsers::where('email', $email)->first();

            $otp = Str::random(6);
            $expiresAt = Carbon::now()->addMinutes(10);

            $otpEntry = OtpPartnerUser::where('email', $user->email)->first();

            if (!$otpEntry) {
                $otpEntry = new OtpPartnerUser();
                $otpEntry->email = $user->email;
            }
            $otpEntry->otp = $otp;

            $otpEntry->expires_at = $expiresAt;

            $otpEntry->save();

            Mail::to($user->email)->send(new OtpMail($otp));

            return redirect()->back()->with('success', 'OTP has been resent successfully.');
        } else {
            return redirect()->back()->with('fail', 'Email address not found. Please log in again.');
        }
    }

    public function changePassword()
    {
        $token = Route::getCurrentRoute()->token;
        return view('partner.change-password', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' =>   ['required', Password::min(6)->letters()->numbers()->symbols()],
        ]);
        $token = PasswordTokenPartnerUser::where('password_token', '=', $request->token)->first();
        $user = PartnerUsers::where('email', '=', $token->email)->first();
        $partner = Partner::where('zoho_cust_id', $user->zoho_cust_id)->first();

        if ($request->password === $request->confirm_password) {
            $token_user = PasswordTokenPartnerUser::where('email', '=', $user->email)->first();
            $token_user->delete();
            $user->password = Hash::make($request->password);
            $user->save();
            if ($user->userLastLoggedIn) {
                return redirect('/login')->with('success', 'Now you can login with your new password');
            } else {
                session()->put('loginId', $user->zoho_cust_id);
                session()->put('loginPartner', $user->email);
                session()->put('userId', $user->zoho_cpid);
                $user->userLastLoggedIn = Carbon::now()->format('Y-m-d H:i:s');
                $user->invitation_status = 'Registered';
                $user->save();
                $partner->status = 'active';
                $partner->save();
                $provider_data = ProviderData::where('zoho_cust_id', $user->zoho_cust_id)->first();
                $availability_data = ProviderAvailabilityData::where('zoho_cust_id', $user->zoho_cust_id)->first();
                $subscription =  Subscriptions::where('zoho_cust_id', $user->zoho_cust_id)->first();
                if ($availability_data === null || $provider_data === null) {
                    return redirect('/provider-info')->with('success', 'Logged in successfully');
                } else {
                    if ($subscription) {
                        return redirect('/')->with('success', 'Logged in successfully');
                    } else {
                        return redirect('/clicks-report')->with('success', 'Logged in successfully');
                    }
                }
            }
        } else {
            return back()->with('fail', 'Password confirmation do not match the password');
        }
    }

    public function forgotPassword(Request $request)
    {
        $app_url = env('APP_URL');
        $user = PartnerUsers::where('email', '=', $request->email)->first();

        if ($user) {
            $token_user = PasswordTokenPartnerUser::where('email', '=', $user->email)->first();
            if ($token_user) {
                $token_user->delete();
            }
            $token = new PasswordTokenPartnerUser();
            $token->email = $user->email;
            $token->password_token =  Str::random(100);
            $token->save();

            $tokens = PasswordTokenPartnerUser::where('email', '=', $user->email)->first();
            $token_1 = $tokens->password_token;
            $email = $user->email;
            $name = $user->first_name;

            $this->sendResetMail($email, $token_1, $app_url, $name);

            return redirect('/reset-mail');
        } else {
            return redirect('/reset-mail');
        }
    }

    public function sendResetMail($email, $token, $app_url, $name)
    {
        Mail::to(users: $email)->send(new ResetPassword($token, $app_url, $name));
        return "Mail send!";
    }


    public function loginAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if ($admin) {
            if (Hash::check($request->password, $admin->password)) {
                if ($admin->admin_last_logged_in) {


                    $otp = Str::random(6);
                    $expiresAt = Carbon::now()->addMinutes(10);

                    $otpEntry = OtpPartnerUser::where('email', $admin->email)->first();

                    if (!$otpEntry) {
                        $otpEntry = new OtpPartnerUser();
                        $otpEntry->email = $admin->email;
                    }

                    $otpEntry->otp = $otp;
                    $otpEntry->expires_at = $expiresAt;
                    $otpEntry->save();

                    Mail::to($admin->email)->send(new OtpMail($otp));

                    return redirect()->route('admin.verify.otp.form')->with('email', $admin->email);
                } else {

                    $token = new PasswordTokenAdmin();
                    $token->email = $admin->email;
                    $token->password_token = Str::random(100);
                    $token->save();

                    return redirect('/admin/change-password/' . $token->password_token);
                }
            } else {
                return back()->with('fail', 'Invalid Password');
            }
        } else {
            return back()->with('fail', 'This email is not registered');
        }
    }



    /*Admin OTP Functions*/
    public function verifyAdminOtpForm()
    {
        return view('admin.verify-otp');
    }

    public function verifyAdminOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $otpEntry = OtpPartnerUser::where('email', $request->email)->where('otp', $request->otp)->first();

        if ($otpEntry && Carbon::now()->lessThanOrEqualTo($otpEntry->expires_at)) {

            $admin = Admin::where('email', $request->email)->first();

            session()->put('loginId', $admin->id);

            session()->put('loginAdmin', $admin->email);

            session()->put('role', $admin->role);

            OtpPartnerUser::where('email', $request->email)->delete();
            return redirect('/admin')->with('success', 'Logged in successfully');
        } else {
            return back()->with('fail', 'Invalid or expired OTP');
        }
    }

    public function resendAdminOtp(Request $request)
    {
        $email = $request->session()->get('email');

        if ($email) {
            $admin = Admin::where('email', $email)->first();

            $otp = Str::random(6);
            $expiresAt = Carbon::now()->addMinutes(10);

            $otpEntry = OtpPartnerUser::where('email', $admin->email)->first();

            if (!$otpEntry) {
                $otpEntry = new OtpPartnerUser();
                $otpEntry->email = $admin->email;
            }

            $otpEntry->otp = $otp;
            $otpEntry->expires_at = $expiresAt;
            $otpEntry->save();

            Mail::to($admin->email)->send(new OtpMail($otp));

            return redirect()->back()->with('success', 'OTP has been resent successfully.');
        } else {
            return redirect()->back()->with('fail', 'Email address not found. Please log in again.');
        }
    }

    //Logout
    public function logout()
    {
        if (Session::has('loginPartner')) {
            if (Session::has('userId')) {
                Session::pull('userId');
            }
            Session::pull('loginId');
            return view('partner.login');
        } else {
            Session::pull('loginId');
            return redirect('/admin/login');
        }
    }

    public function resetView()
    {
        return view('partner.reset-password');
    }

    public function resetMail()
    {
        return view('partner.reset-mail');
    }

    public function adminForgotPassword(Request $request)
    {
        $app_url = env('APP_URL');
        $admin = Admin::where('email', '=', $request->email)->first();

        if ($admin) {
            $token_user = PasswordTokenAdmin::where('email', '=', $admin->email)->first();
            if ($token_user) {
                $token_user->delete();
            }
            $token = new PasswordTokenAdmin();
            $token->email = $admin->email;
            $token->password_token =  Str::random(100);
            $token->save();

            $tokens = PasswordTokenAdmin::where('email', '=', $admin->email)->first();
            $token_1 = $tokens->password_token;
            $email = $admin->email;
            $name = $admin->admin_name;

            $this->sendAdminResetMail($email, $token_1, $app_url, $name);

            return redirect('/admin/reset-mail');
        } else {
            return redirect('/admin/reset-mail');
        }
    }

    public function sendAdminResetMail($email, $token, $app_url, $name)
    {
        Mail::to(users: $email)->send(new AdminResetPassword($token, $app_url, $name));
        return "Mail send!";
    }

    public function adminChangePassword()
    {
        $token = Route::getCurrentRoute()->token;
        return view('admin.change-password', compact('token'));
    }

    public function adminResetPassword(Request $request)
    {
        $request->validate([
            'password' =>   ['required', Password::min(6)->letters()->numbers()->symbols()],
        ]);
        $token = PasswordTokenAdmin::where('password_token', '=', $request->token)->first();
        $admin = Admin::where('email', '=', $token->email)->first();

        if ($request->password === $request->confirm_password) {
            $token_admin = PasswordTokenAdmin::where('email', '=', $admin->email)->first();
            $token_admin->delete();
            $admin->password = Hash::make($request->password);
            $admin->save();
            if ($admin->admin_last_logged_in) {
                return redirect('/admin/login')->with('success', 'Now you can login with your new password');
            } else {
                session()->put('loginId', $admin->id);

                session()->put('loginAdmin', $admin->email);

                session()->put('role', $admin->role);

                $admin->admin_last_logged_in = Carbon::now()->format('Y-m-d H:i:s');
                $admin->save();
                return redirect('/admin');
            }
        } else {
            return back()->with('fail', 'Password confirmation do not match the password');
        }
    }

    public function adminResetView()
    {
        return view('admin.reset-password');
    }

    public function adminResetMail()
    {
        return view('admin.reset-mail');
    }
}
