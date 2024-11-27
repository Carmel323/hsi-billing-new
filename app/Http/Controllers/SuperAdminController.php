<?php

namespace App\Http\Controllers;

use App\Mail\AdminInvite;
use App\Models\Admin;
use App\Models\MailNotifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class SuperAdminController extends Controller
{
    public function getAdmins(Request $request)
    {
        if (Session::get('role') === 'SuperAdmin') {

            $query = Admin::query();

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if ($startDate && $endDate) {

                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }

            if ($request->has('search')) {

                $search = $request->input('search');

                $query->where(function ($q) use ($search) {

                    $q->where('admin_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('role', 'LIKE', "%{$search}%");
                });
            }

            $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

            $admins = $query->orderByDesc('id')->paginate($perPage);

            return view('admin.admins', compact('admins'));
        } else {
            return redirect('/incorrect-superadmin-user');
        }
    }

    public function inviteAdmin(Request $request)
    {
        $admin = Admin::where('email', $request->email)->first();
        if ($admin) {
            return back()->with('fail', 'Email already exists');
        }

        $unhashedPassword = Str::random(16);
        $new_admin = new Admin();
        $new_admin->admin_name = $request->admin_name;
        $new_admin->email = $request->email;
        $new_admin->password = Hash::make($unhashedPassword);
        $new_admin->role = $request->role;
        $new_admin->receive_mails = $request->receive_mails ? 'Yes' : 'No';
        $new_admin->save();

        $mail_notifications = new MailNotifications();
        $mail_notifications->admin_id = $new_admin->id;
        $mail_notifications->partner_signup_mail = $request->partner_signup_mail ? true : false;
        $mail_notifications->plan_purchase_mail = $request->plan_purchase_mail ? true : false;
        $mail_notifications->clicks_alert_mail = $request->clicks_alert_mail ? true : false;
        $mail_notifications->support_ticket_mail = $request->support_ticket_mail ? true : false;
        $mail_notifications->data_submission_mail = $request->data_submission_mail ? true : false;
        $mail_notifications->save();

        if ($new_admin->role === "AlertRecipient") {
            return redirect('admin/admins')->with('success', 'AlertRecipient added successfully');
        }

        $this->sendEmail($request->admin_name, $request->email, $unhashedPassword);

        return redirect('admin/admins')->with('success', 'Admin invited successfully');
    }

    public function sendEmail($name, $email, $password)
    {
        $app_url = env('APP_URL');

        try {
            Mail::to($email)->send(new AdminInvite($app_url, $name, $password));
            return "Mail send!";
        } catch (\Exception $e) {

            Log::error('Failed to send email to ' . $email . '. Error: ' . $e->getMessage());

            return "Failed to send email";
        }
    }

    public function deleteAdmin()
    {
        $id = Route::getCurrentRoute()->id;
        $admin = Admin::where('id', $id)->first();
        $admin->delete();
        return redirect('admin/admins')->with('success', 'Admin Deleted Successfully');
    }

    public function updateAdmin(Request $request)
    {
        $id = $request->id;
        $admin = Admin::where('id', $id)->first();
        $admin->admin_name = $request->admin_name;
        $admin->role = $request->role;
        $admin->receive_mails = $request->receive_mails ? 'Yes' : 'No';
        $admin->save();
        $mail_notifications = MailNotifications::where('admin_id', $id)->first();
        if ($mail_notifications === null) {
            $mail_notifications = new MailNotifications();
        }
        $mail_notifications->admin_id = $id;
        $mail_notifications->partner_signup_mail = $request->partner_signup_mail ? true : false;
        $mail_notifications->plan_purchase_mail = $request->plan_purchase_mail ? true : false;
        $mail_notifications->clicks_alert_mail = $request->clicks_alert_mail ? true : false;
        $mail_notifications->support_ticket_mail = $request->support_ticket_mail ? true : false;
        $mail_notifications->data_submission_mail = $request->data_submission_mail ? true : false;
        $mail_notifications->save();
        return redirect('admin/admins')->with('success', 'Admin Updated Successfully');
    }

    public function viewEditAdmin()
    {
        $id = Route::getCurrentRoute()->id;
        $admin = Admin::find($id);
        $mail_notifications = MailNotifications::where('admin_id', $id)->first();
        return view('admin.admin-edit', compact('admin', 'mail_notifications'));
    }

    public function viewInviteAdmin()
    {
        return view('admin.admin-invite');
    }
}
