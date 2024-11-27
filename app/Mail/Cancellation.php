<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Cancellation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */


    public $plan_name;
    public $plan_price;
    public $subscription_number;
    public $name;
    public $request_raised_by;
    public $company_name;
    public function __construct($plan_name, $plan_price, $subscription_number, $name, $request_raised_by, $company_name)
    {

        $this->plan_name = $plan_name;
        $this->plan_price = $plan_price;
        $this->subscription_number = $subscription_number;
        $this->name = $name;
        $this->request_raised_by = $request_raised_by;
        $this->company_name =  $company_name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Subscription Cancellation for' . ' ' . $this->company_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.cancel',
        );
    }

    public function build()
    {
        return $this->view(view: 'mails.cancel');
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
