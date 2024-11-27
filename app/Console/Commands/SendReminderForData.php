<?php

namespace App\Console\Commands;

use App\Mail\ReminderMailForData;
use App\Models\Partner;
use App\Models\PartnerUsers;
use App\Models\ProviderAvailabilityData;
use App\Models\ProviderData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReminderForData extends Command
{
    protected $signature = 'data:send-reminder';
    protected $description = 'Send reminder emails to users who haven\'t uploaded provider data and company info in 48 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $partners = Partner::where('created_at', '<=', now()->subHours(48))->get();

        foreach ($partners as $partner) {
            $provider_data = ProviderAvailabilityData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
            $company_info = ProviderData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
            $partner_user =  PartnerUsers::where('zoho_cust_id', $partner->zoho_cust_id)->where('is_primary', true)->first();
            $name = $partner_user->first_name . ' ' . $partner_user->last_name;
            if ($provider_data === null || $company_info === null) {
                Mail::to($partner_user->email)->send(new ReminderMailForData($name));
            }
        }
        $this->info('Reminder emails for data sent successfully!');
    }
}
