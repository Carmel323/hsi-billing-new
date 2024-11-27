<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminInvite extends Mailable
{
    use Queueable, SerializesModels;

    public $app_url;
    public $name;
    public $password;
    public function __construct($app_url, $name, $password)
    {
        $this->app_url = $app_url;
        $this->name = $name;
        $this->password = $password;
    }


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation to join our platform',
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'mails.admin-invite',
        );
    }

    public function build()
    {
        return $this->view('mails.admin-invite')->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    }



    public function attachments(): array
    {
        return [];
    }
}
