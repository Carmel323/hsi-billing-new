<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Signup extends Mailable
{
    use Queueable, SerializesModels;


    public $partner_company;
    public $partner_username;
    public $partner_email;
    public $partner_ph_number;
    public $name;

    public function __construct($partner_company, $partner_username, $partner_email, $partner_ph_number, $name)
    {

        $this->partner_company = $partner_company;
        $this->partner_username = $partner_username;
        $this->partner_email = $partner_email;
        $this->partner_ph_number = $partner_ph_number;
        $this->name = $name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Partner Signup',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.signup',
        );
    }


    public function build()
    {
        return $this->view(view: 'mails.signup');
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
