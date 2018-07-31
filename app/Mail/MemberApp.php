<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MemberApp extends Mailable
{
    use Queueable, SerializesModels;

    public $form_data;
    public $name;
    public $photo;
    public $destination;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email_data, $destination = 'members')
    {
        // include form data and destination (members / admin / applicant) to filter info as needed
        $this->form_data = $email_data['form_data'];
        $this->name = $email_data['name'];
        $this->photo = $email_data['photo'];
        $this->destination = $destination;
        $this->mail_attributes = $email_data['mail_attributes'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $this->to($this->mail_attributes['to']);
        $this->subject($this->mail_attributes['subject']);

        if ($this->mail_attributes['cc'] != NULL) { $this->cc($this->mail_attributes['cc']); }
        if ($this->mail_attributes['replyto'] != NULL) { $this->replyTo($this->mail_attributes['replyto']); }

        return $this->markdown('emails.memberapp');

    }
}
