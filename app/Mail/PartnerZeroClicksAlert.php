<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerZeroClicksAlert extends Mailable
{
    use Queueable, SerializesModels;
    public $partnerIdsWithNoClicks;
    public $admin_name;

    public function __construct($partnerIdsWithNoClicks, $admin_name)
    {
        $this->partnerIdsWithNoClicks = $partnerIdsWithNoClicks;
        $this->admin_name = $admin_name;
    }

    public function build()
    {
        return $this->subject('Partner With Zero Clicks Data From ' . Carbon::now()->subDays(7)->toDateString() . ' ' . 'To ' . ' ' . Carbon::now()->toDateString())->view('mails.partner-clicks-weekly-alert')->with('partnerIdsWithNoClicks', 'admin_name', $this->partnerIdsWithNoClicks, $this->admin_name);
    }
}
