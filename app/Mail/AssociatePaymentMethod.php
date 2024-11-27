<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssociatePaymentMethod extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $name;
    public $link;

    public function __construct($email, $name, $link)
    {
        $this->email = $email;
        $this->name = $name;
        $this->link = $link;
    }


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Associate Payment Method',
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'mails.associate-payment',
        );
    }

    public function build()
    {
        return $this->view(view: 'mails.associate-payment');
    }

    public function attachments(): array
    {
        return [];
    }
}
