<?php

namespace App\Http\Controllers;

use App\Models\PartnerClicksView;
use App\Models\ClicksEmailLog;
use App\Mail\ClicksUsageAlertMail;
use App\Models\Admin;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ClicksEmailAlertController extends Controller
{
    public function sendAlerts()
    {
        $percentRanges = [
            '85 to 95' => [85, 95],
            '95 to 100' => [95, 100],
            '100 and above' => [100, PHP_INT_MAX]
        ];

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $partnerClicks = PartnerClicksView::where('click_month', $currentMonth)
            ->where('click_year', $currentYear)
            ->get();

        foreach ($partnerClicks as $click) {
            foreach ($percentRanges as $label => $range) {
                if ($click->clicks_usage_percentage >= $range[0] && $click->clicks_usage_percentage < $range[1]) {
                    $this->sendEmailAlert($click, $label);
                }
            }
        }
    }

    private function sendEmailAlert($click, $clicksUsagePercentRange)
    {
        $emailCount = ClicksEmailLog::where('partner_id', $click->partner_id)
            ->where('clicks_month', $click->click_month)
            ->where('clicks_year', $click->click_year)
            ->where(function ($query) use ($clicksUsagePercentRange, $click) {
                $query->whereJsonContains('details->clicks_usage_percent_range', $clicksUsagePercentRange)
                    ->whereJsonContains('details->max_allowed_clicks', $click->max_allowed_clicks);
            })
            ->count();

        if ($emailCount >= 1) {
            return;
        }

        $details = [
            'partner_name' => $click->partner_company_name,
            'partner_email' => $click->primary_contact_email,
            'subscribed_plan' => $click->subscribed_plan_name,
            'addon' => $click->subscribed_addon_name,
            'clicks_month' => $click->click_month,
            'clicks_year' => $click->click_year,
            'plan_max_clicks' => $click->plan_max_clicks ?? 0,
            'addon_max_clicks' => $click->addon_max_clicks ?? 0,
            'max_allowed_clicks' => ($click->plan_max_clicks ?? 0) + ($click->addon_max_clicks ?? 0),
            'clicks_count' => $click->unique_clicks_count,
            'usage_percentage' => $click->clicks_usage_percentage,
            'clicks_usage_percent_range' => $clicksUsagePercentRange,
            'timestamp' => now()->toDateTimeString(),
        ];

        $admins = Admin::where('receive_mails', 'Yes')->get();
        $admin_emailLogDetails = [];

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(new ClicksUsageAlertMail($details));
                $status = 'sent';
            } catch (\Exception $e) {
                \Log::error('Mail sending failed: ' . $e->getMessage());
                $status = 'error';
            }

            $admin_emailLogDetails[] = [
                'email' => $admin->email,
                'email_status' => $status
            ];
        }

        $details['admin_email_status'] = $admin_emailLogDetails;

        $clicksEmailLog = new ClicksEmailLog();
        $clicksEmailLog->partner_id = $click->partner_id;
        $clicksEmailLog->clicks_month = $click->click_month;
        $clicksEmailLog->clicks_year = $click->click_year;
        $clicksEmailLog->details = json_encode($details);
        $clicksEmailLog->timestamp = now();
        $clicksEmailLog->save();
    }

    public function runScheduledTask()
    {
        Artisan::call('clicks:send-alerts');
        return response()->json(['message' => 'Scheduled task executed successfully.']);
    }
}
