<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MemberApp extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $form_data;

    public $name;

    public $photo;

    public $destination;

    public $skip_fields;

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
        $this->skip_fields = $email_data['skip_fields'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        switch ($this->destination) {
            case 'admin':
                $this->to(config('kwartzlabos.membership_app.admin.to'));
                $this->subject(config('kwartzlabos.membership_app.admin.subject').' - '.$this->name);
                if (config('kwartzlabos.membership_app.admin.cc') != null) {
                    $this->cc(config('kwartzlabos.membership_app.admin.cc'));
                }
                if (config('kwartzlabos.membership_app.admin.replyto') != null) {
                    $this->replyto(config('kwartzlabos.membership_app.admin.replyto'));
                }
                break;
            case 'members':
                $this->to(config('kwartzlabos.membership_app.members.to'));
                $this->subject(config('kwartzlabos.membership_app.members.subject').' - '.$this->name);
                if (config('kwartzlabos.membership_app.members.cc') != null) {
                    $this->cc(config('kwartzlabos.membership_app.members.cc'));
                }
                if (config('kwartzlabos.membership_app.members.replyto') != null) {
                    $this->replyto(config('kwartzlabos.membership_app.members.replyto'));
                }
                break;
        }

        // only send the message if there is a valid email address from the config, otherwise skip
        if (count($this->to) > 0) {
            return $this->view('emails.memberapp')
                        ->text('emails.memberapp_plain');
        }
    }
}
