<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerPurchasedPlan extends Mailable
{
    use Queueable, SerializesModels;


    public $partner_name;
    public $partner_email;
    public $partner_company;
    public $name;
    public $plan_name;
    public $plan_price;

    public function __construct($partner_name, $partner_email, $partner_company, $name, $plan_name, $plan_price)
    {
        $this->partner_name = $partner_name;
        $this->partner_email = $partner_email;
        $this->partner_company = $partner_company;
        $this->name = $name;
        $this->plan_name = $plan_name;
        $this->plan_price = $plan_price;
    }


    public function envelope(): Envelope
    {
        return new Envelope(

            subject: 'Partner Selected a Plan and added Payment method -' . $this->partner_company . ' ',
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'mails.partner-purchased-plan',
        );
    }

    public function build()
    {
        return $this->view(view: 'mails.partner-purchased-plan');
    }


    public function attachments(): array
    {
        return [];
    }
}
