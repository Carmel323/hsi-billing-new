<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CreateSubscription extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $plan_name;
    public $plan_price;
    public $name;
    public $link;

    public function __construct($email, $name, $plan_name, $plan_price, $link)
    {
        $this->email = $email;
        $this->plan_name = $plan_name;
        $this->plan_price = $plan_price;
        $this->name = $name;
        $this->link = $link;
    }

   
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pay and Create Subscription',
        );
    }

   
    public function content(): Content
    {
        return new Content(
            view: 'mails.createsubscription',
        );
    }

    public function build()
    {
        return $this->view(view: 'mails.createsubscription');
    }

    public function attachments(): array
    {
        return [];
    }
}
