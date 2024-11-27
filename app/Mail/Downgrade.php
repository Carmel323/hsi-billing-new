<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Downgrade extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $current_plan;
    public $next_plan;
    public $name;
    public $request_raised_by;
    public $company_name;
    public function __construct($current_plan, $next_plan, $name, $request_raised_by, $company_name)
    {
        $this->current_plan = $current_plan;
        $this->next_plan = $next_plan;
        $this->name = $name;
        $this->request_raised_by = $request_raised_by;
        $this->company_name = $company_name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Subscription Downgrade for' . ' ' . $this->company_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.downgrade',
        );
    }

    public function build()
    {
        return $this->view(view: 'mails.downgrade');
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
