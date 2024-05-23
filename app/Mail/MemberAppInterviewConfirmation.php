<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberAppInterviewConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $email_data)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: 'membership@kwartzlab.ca',
            to: $this->email_data['form_data']['email']['value'],
            subject: 'Application Received - Kwartzlab Makerspace',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.member-app.confirmation',
        );
    }
}
