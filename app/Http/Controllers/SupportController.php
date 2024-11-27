<?php

namespace App\Http\Controllers;

use App\Mail\AdminSupport;
use App\Mail\Cancellation;
use App\Mail\Downgrade;
use App\Mail\Email;
use App\Models\Partner;
use App\Models\Subscriptions;
use App\Models\AccessToken;
use App\Models\PartnerAddress;
use App\Models\AddOn;
use App\Models\Admin;
use App\Models\enterprise;
use App\Models\PartnerUsers;
use App\Models\ProviderAvailabilityData;
use App\Models\ProviderData;
use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Exception\GuzzleException;
use Exception;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{

  public function getSupport(Request $request)
  {
    if (Session::has('loginPartner')) {

      try {

        $partnerId = Session::get('loginId');

        $query = DB::table('supports')->where('supports.zoho_cust_id', '=', $partnerId);

        $startDate = $request->input('start_date');

        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {

          $query->whereDate('supports.created_at', '>=', $startDate)->whereDate('supports.created_at', '<=', $endDate);
        }

        if ($request->has('search')) {

          $search = $request->input('search');

          $query->where(function ($q) use ($search) {

            $q->where('subscription_number', 'LIKE', "%{$search}%")->orWhere('message', 'LIKE', "%{$search}%")->orWhere('request_type', 'LIKE', "%{$search}%");
          });
        }

        $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

        $supports = $query->orderByDesc('supports.created_at')->paginate($perPage);

        $supportsArray = $supports->items();

        $zohoCustIds = array_unique(array_column($supportsArray, 'zoho_cust_id'));

        $partners = DB::table('partners')->whereIn('zoho_cust_id', $zohoCustIds)->get()->keyBy('zoho_cust_id');


        foreach ($supportsArray as $support) {

          if (isset($partners[$support->zoho_cust_id])) {

            $support->partner = $partners[$support->zoho_cust_id];
          } else {

            $support->partner = null;
          }
        }

        $showModal = false;
        $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();
        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', Session::get('loginId'))->first();
        $company_info = ProviderData::where('zoho_cust_id', Session::get('loginId'))->first();

        // if ($availability_data === null || $company_info === null) {
        //   $showModal = true;
        // }

        return view('partner.support', compact('supports', 'showModal', 'availability_data', 'company_info'));
      } catch (Exception $e) {

        return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
      }
    }
  }

  public function downgrade(Request $request, Support $support)
  {
    $support = Support::where('zoho_cust_id', '=', Session::get('loginId'))
      ->where('request_type', '=', 'Downgrade')
      ->where('status', '=', 'open')
      ->first();

    if (!$support) {

      if (Session::has('loginId')) {

        $subscription =   DB::table('subscriptions')
          ->join('plans', 'subscriptions.plan_id', '=', 'plans.plan_id')
          ->select('plans.*', 'subscriptions.*')
          ->where('zoho_cust_id', '=', Session::get('loginId'))
          ->first();

        $partner = Partner::where('zoho_cust_id', '=', Session::get('loginId'))->first();
      }

      $support = new Support();
      $support->date = date('d-M-Y');
      $support->request_type = 'Downgrade';
      $support->subscription_number = $subscription->subscription_number;
      $support->message = "I would like to downgrade my subscription to the $request->plan_name plan. Please contact me with steps to downgrade.";
      $support->status = "open";
      $support->zoho_cust_id = $partner->zoho_cust_id;
      $support->zoho_cpid = Session::get('userId');
      $support->attributes = [
        'current_plan' => $subscription->plan_name,
        'next_plan' => $request->plan_name,
      ];
      $support->save();
      $current_partner_user = PartnerUsers::where('zoho_cpid',  $support->zoho_cpid)->first();
      $partner_users = PartnerUsers::where('zoho_cust_id',  $support->zoho_cust_id)->where('status', 'active')->get();



      $current_plan = $subscription->plan_name;
      $next_plan = $request->plan_name;
      $ticket_raised_by = $current_partner_user->email;
      $company_name = $partner->company_name;


      $this->sendDowngradeEmail($partner_users, $current_plan, $next_plan, $ticket_raised_by, $company_name);

      $this->sendMailToAdmin($support, $partner, $current_partner_user);

      return redirect('/support')->with('success', 'Your request has been recorded. We will get back to you shortly!');
    } else {

      return back()->with('fail', 'You already raised a support ticket');
    }
  }


  public function sendDowngradeEmail($partner_users, $current_plan, $next_plan, $request_raised_by, $company_name)
  {
    foreach ($partner_users as $user) {
      $name = $user->first_name . ' ' . $user->last_name;
      Mail::to(users: $user->email)->send(new Downgrade($current_plan, $next_plan, $name, $request_raised_by, $company_name));
    }
  }


  //Validate Cancellation Request and Trigger mail

  public function cancellation()
  {
    $support = Support::where('zoho_cust_id', '=', Session::get('loginId'))
      ->where('request_type', '=', 'Cancellation')
      ->where('status', '=', 'open')
      ->first();

    if (!$support) {

      if (Session::has('loginId')) {

        $subscription =  DB::table('subscriptions')
          ->join('plans', 'subscriptions.plan_id', '=', 'plans.plan_id')
          ->select('plans.*', 'subscriptions.*')
          ->where('zoho_cust_id', '=', Session::get('loginId'))
          ->first();

        $partner = Partner::where('zoho_cust_id', '=', Session::get('loginId'))->first();
      }

      $support = new Support();
      $support->date = date('d-M-Y');
      $support->request_type = 'Cancellation';
      $support->subscription_number = $subscription->subscription_number;
      $support->message = "I would like to cancel my existing subscription($subscription->subscription_number). Please contact me with next steps for cancellation.";
      $support->status = "open";
      $support->zoho_cust_id = $partner->zoho_cust_id;
      $support->zoho_cpid = Session::get('userId');
      $support->save();

      $current_partner_user = PartnerUsers::where('zoho_cpid',  $support->zoho_cpid)->first();
      $partner_users = PartnerUsers::where('zoho_cust_id', $support->zoho_cust_id)->where('status', 'active')->get();
      $ticket_raised_by = $current_partner_user->email;



      $plan_name = $subscription->plan_name;
      $plan_price = $subscription->price;
      $subscription_number = $subscription->subscription_number;
      $company_name = $partner->company_name;

      $this->sendCancelEmail($partner_users, $plan_name, $plan_price, $subscription_number, $ticket_raised_by, $company_name);

      $this->sendMailToAdmin($support, $partner, $current_partner_user);

      return redirect('/support')->with('success', 'Your request has been recorded. We will get back to you shortly!');
    } else {
      return back()->with('fail', 'You already raised a support ticket');
    }
  }

  public function sendCancelEmail($partner_users, $plan_name, $plan_price, $subscription_number, $request_raised_by, $company_name)
  {
    foreach ($partner_users as $user) {
      $name = $user->first_name . ' ' . $user->last_name;
      Mail::to(users: $user->email)->send(new Cancellation($plan_name, $plan_price, $subscription_number, $name, $request_raised_by, $company_name));
    }
  }

  //Create Enterprise Plan Support Ticket
  public function enterpriseSupport(Request $request, AccessToken $token)
  {

    $support = Support::where('zoho_cust_id', '=', Session::get('loginId'))
      ->where('request_type', '=', 'Custom Enterprise')
      ->where('status', '=', 'open')
      ->first();

    if (!$support) {
      if (Session::has('loginId')) {
        $partner = Partner::where('zoho_cust_id', '=', Session::get('loginId'))->first();
        $subscription =  Subscriptions::where('zoho_cust_id', '=', Session::get('loginId'))
          ->where('status', '=', 'live')
          ->first();
        $company_name = $partner->company_name;
      }

      $support = new Support();
      $support->date = date('d-M-Y');
      $support->request_type = 'Custom Enterprise';
      if ($subscription) {
        $support->subscription_number = $subscription->subscription_number;
      }
      $support->message = $request->message;
      $support->status = "open";
      $support->zoho_cust_id = $partner->zoho_cust_id;
      $support->zoho_cpid = Session::get('userId');
      $support->save();

      $current_partner_user = PartnerUsers::where('zoho_cpid', Session::get('userId'))->first();

      $this->sendMailToAdmin($support, $partner, $current_partner_user);

      return redirect('/support');
    } else {
      return back()->with('fail', 'You already raised a support ticket');
    }
  }

  public function customSupport(Request $request, AccessToken $token)
  {

    $support = Support::where('zoho_cust_id', '=', Session::get('loginId'))
      ->where('request_type', '=', 'Custom Support')
      ->where('status', '=', 'open')
      ->first();

    if (!$support) {
      if (Session::has('loginId')) {
        $partner = Partner::where('zoho_cust_id', '=', Session::get('loginId'))->first();
        $subscription =  Subscriptions::where('zoho_cust_id', '=', Session::get('loginId'))
          ->where('status', '=', 'live')
          ->first();
        $company_name = $partner->company_name;
      }

      $support = new Support();
      $support->date = date('d-M-Y');
      $support->request_type = 'Custom Support';
      $support->message = $request->message;
      $support->status = "open";
      $support->zoho_cust_id = $partner->zoho_cust_id;
      $support->zoho_cpid = Session::get('userId');
      $support->save();

      $current_partner_user = PartnerUsers::where('zoho_cpid', Session::get('userId'))->first();



      $this->sendMailToAdmin($support, $partner, $current_partner_user);

      return redirect('/support');
    } else {
      return back()->with('fail', 'You already raised a support ticket');
    }
  }

  public function revokeSupport(Request $request)
  {
    $support = Support::where('id', '=', $request->id)->first();
    $support->status = 'Revoked';
    $support->comments = $request->comments;
    $support->save();
    return redirect('/admin/support')->with('success', 'Revoked successfully');
  }

  public function filter(Request $request)
  {
    $supports = Support::whereDate('created_at', '>=', $request->start_date)
      ->whereDate('created_at', '<=', $request->end_date)
      ->get();
    return view('partner.support', compact('supports'));
  }

  public function perPage(Request $request)
  {
    $perPage = $request->input('per_page', 10);
    $supports = Support::paginate($perPage);
    return view('partner.support', compact('supports'));
  }

  public function sendMailToAdmin($support, $partner, $partner_user)
  {
    $admins = Admin::where('receive_mails', 'Yes')->get();

    $request_type = $support->request_type;
    $req_message = $support->message;
    $subscription_number = $support->subscription_number;
    $company_name = $partner->company_name;

    foreach ($admins as $admin) {
      Mail::to(users: $admin->email)->send(new AdminSupport($partner_user->email, $request_type, $req_message, $subscription_number, $admin->admin_name, $company_name));
    }
  }

  public function getSupportOld(Request $request)
  {
    if (Session::has('loginPartner')) {

      try {

        $partnerId = Session::get('loginId');

        $query = DB::table('supports')
          ->where('supports.zoho_cust_id', '=',   $partnerId);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
          $query->whereDate('supports.created_at', '>=', $startDate)->whereDate('supports.created_at', '<=', $endDate);
        }

        if ($request->has('search')) {

          $search = $request->input('search');

          $query->where(function ($q) use ($search) {

            $q->where('subscription_number', 'LIKE', "%{$search}%")
              ->orWhere('message', 'LIKE', "%{$search}%")
              ->orWhere('request_type', 'LIKE', "%{$search}%");
          });
        }

        $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

        $supports = $query->orderByDesc('supports.created_at')->paginate($perPage);

        $supportsArray = $supports->items();

        foreach ($supportsArray as $support) {

          $support->partner = DB::table('partners')->where('zoho_cust_id', $support->zoho_cust_id)->first();
        }


        return view('partner.support', compact('supports'));
      } catch (Exception $e) {

        return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
      }
    }
  }
}
