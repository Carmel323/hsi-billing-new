<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerCompletedSignup extends Mailable
{
    use Queueable, SerializesModels;


    public $partner_name;
    public $partner_email;
    public $partner_company;
    public $name;

    public function __construct($partner_name, $partner_email, $partner_company, $name)
    {
        $this->partner_name = $partner_name;
        $this->partner_email = $partner_email;
        $this->partner_company = $partner_company;
        $this->name = $name;
    }


    public function envelope(): Envelope
    {
        return new Envelope(

            subject: 'New Partner Signup -' . $this->partner_company . ' ',
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'mails.partner-completed-signup',
        );
    }

    public function build()
    {
        return $this->view(view: 'mails.partner-completed-signup');
    }


    public function attachments(): array
    {
        return [];
    }
}
