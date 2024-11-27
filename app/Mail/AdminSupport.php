<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminSupport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $email;
    public $request_type;
    public $req_message;
    public $subscription_number;
    public $company_name;
    public $name;
    public function __construct($email, $request_type, $req_message, $subscription_number, $name, $company_name)
    {
        $this->email = $email;
        $this->request_type = $request_type;
        $this->req_message = $req_message;
        $this->subscription_number = $subscription_number;
        $this->company_name = $company_name;
        $this->name = $name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Support Request from' . ' ' . $this->company_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.admin-support',
        );
    }

    public function build()
    {
        return $this->view(view: 'mails.admin-support');
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
