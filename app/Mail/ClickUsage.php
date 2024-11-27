<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClickUsage extends Mailable
{
    use Queueable, SerializesModels;

    public $usage_percent;
    public $name;

    public function __construct($usage_percent, $name)
    {
        $this->usage_percent = $usage_percent;
        $this->name = $name;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Clicks Usage Reminder: ' . $this->usage_percent . '% Usage',
        );
    }

    public function content(): Content
    {
         return new Content(
            view: 'mails.clicks_usage',
        );
    }

    public function build()
    {
        return $this->view('mails.clicks_usage')
            ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    }

    public function attachments(): array
    {
        return [];
    }
}
