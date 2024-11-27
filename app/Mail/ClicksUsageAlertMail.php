<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClicksUsageAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function build()
    {
        $usagePercentage = number_format($this->details['usage_percentage'], 2);
        $partnerName = $this->details['partner_name'];

        $percentRanges = [
            '85 to 95' => [85, 95],
            '95 to 100' => [95, 100],
            '100 and above' => [100, PHP_INT_MAX]
        ];

        $subject = "Clicks Usage Alert for $partnerName";

        foreach ($percentRanges as $range => $limits) {
            if ($usagePercentage >= $limits[0] && $usagePercentage < $limits[1]) {
                switch ($range) {
                    
                    case '85 to 95':
                    
                    $subject = "$partnerName used $usagePercentage% Clicks 📈";

                        break;

                    case '95 to 100':
                        $subject = "$partnerName used $usagePercentage% Clicks ⚠️ ";
                        break;

                    case '100 and above':
                        $subject = "$partnerName used $usagePercentage% Clicks 🚨"; 
                        break;
                }
                break;
            }
        }

        return $this->view('mails.clicks_usage')
            ->subject($subject);
    }
}
