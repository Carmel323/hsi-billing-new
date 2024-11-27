<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Email extends Mailable
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


    public function envelope(): Envelope
    {

        return new Envelope(
            subject: "Invitation to join Clearlink ISP Partner Program",
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'mails.invite',
        );
    }

    public function build()
    {
        return $this->view('mails.invite')->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    }



    public function attachments(): array
    {
        return [];
    }
}
