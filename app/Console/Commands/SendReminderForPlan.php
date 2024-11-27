<?php

namespace App\Console\Commands;

use App\Mail\ReminderMailForPlan;
use App\Models\Partner;
use App\Models\PartnerUsers;
use App\Models\SelectedPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReminderForPlan extends Command
{
    protected $signature = 'plans:send-reminder';
    protected $description = 'Send reminder emails to users who haven\'t purchasd plan in 48 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $partners = Partner::where('created_at', '<=', now()->subHours(48))->get();

        foreach ($partners as $partner) {
            $selected_plan = SelectedPlan::where('zoho_cust_id', $partner->zoho_cust_id)->first();
            $partner_user =  PartnerUsers::where('zoho_cust_id', $partner->zoho_cust_id)->where('is_primary', true)->first();
            $name = $partner_user->first_name . ' ' . $partner_user->last_name;
            if ($selected_plan === null) {
                Mail::to($partner_user->email)->send(new ReminderMailForPlan($name));
            }
        }
        $this->info('Reminder emails for plans sent successfully!');
    }
}
