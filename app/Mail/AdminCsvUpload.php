<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminCsvUpload extends Mailable
{
    use Queueable, SerializesModels;


    public $file_url;
    public $partner_name;
    public $partner_email;
    public $partner_company;
    public $name;
    public $file_name;
    public $presigned_url;
    public $logo_presigned_url;
    public $landing_page_url;
    public $url;
    public $tune_link;
    public function __construct($file_url, $partner_name, $partner_email, $partner_company, $name, $file_name, $presigned_url, $logo_presigned_url, $landing_page_url, $url, $tune_link)
    {
        $this->file_url = $file_url;
        $this->partner_name = $partner_name;
        $this->partner_email = $partner_email;
        $this->partner_company = $partner_company;
        $this->name = $name;
        $this->file_name = $file_name;
        $this->presigned_url = $presigned_url;
        $this->logo_presigned_url = $logo_presigned_url;
        $this->landing_page_url = $landing_page_url;
        $this->url = $url;
        $this->tune_link = $tune_link;
    }


    public function envelope(): Envelope
    {
        return new Envelope(

            subject: 'Partner Data Has Been Submitted - ' . $this->partner_company . ' ',
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'mails.admin-csv-upload',
        );
    }

    public function build()
    {
        return $this->view(view: 'mails.admin-csv-upload');
    }


    public function attachments(): array
    {
        return [];
    }
}
