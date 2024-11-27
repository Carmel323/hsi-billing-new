<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerSetup extends Mailable
{
    use Queueable, SerializesModels;

    public $app_url;
    public $name;
    public $password;
    public $company_name;
    public function __construct($app_url, $name, $password, $company_name)
    {
        $this->app_url = $app_url;
        $this->name = $name;
        $this->password = $password;
        $this->company_name = $company_name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Partner setup is completed and you can purchase subscription',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.partner-setup',
        );
    }

    public function build()
    {
        return $this->view('mails.partner-setup')->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
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
