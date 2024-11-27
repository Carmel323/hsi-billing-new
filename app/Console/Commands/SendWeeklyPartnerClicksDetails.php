<?php

namespace App\Console\Commands;

use App\Mail\PartnerClicksWeeklyAlert;
use App\Mail\PartnerZeroClicksAlert;
use App\Models\Admin;
use App\Models\Clicks;
use App\Models\Partner;
use App\Models\PartnersAffiliates;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyPartnerClicksDetails extends Command
{
    protected $signature = 'clicks:weekly-alerts';

    protected $description = 'Send weekly partner clicks alerts for admins';

    public function handle()
    {
        $partnerIdsWithNoClicks = [];
        $partners = Partner::all();
        $sevenDaysAgo = Carbon::now()->subDays(7);
        foreach ($partners as $partner) {
            $affiliateIds = PartnersAffiliates::where('partner_id', $partner->id)->pluck('id');

            $clickCount = Clicks::whereIn('partners_affiliates_id', $affiliateIds)
                ->where('click_ts', '>=', $sevenDaysAgo)
                ->count();

            if ($clickCount === 0) {
                $partnerIdsWithNoClicks[] = [
                    'partner_id' => $partner->id,
                    'partner_name' => $partner->company_name
                ];
            }
        }

        $admins = Admin::where('receive_mails', 'Yes')
            ->whereHas('mailNotifications', function ($query) {
                $query->where('clicks_alert_mail', true);
            })
            ->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new PartnerZeroClicksAlert($partnerIdsWithNoClicks, $admin->admin_name));
        }

        $this->info('Clicks weekly alerts sent successfully.');
    }
}
