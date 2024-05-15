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

    public $recipient;

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
        if (isset($email_data['recipient'])) {
            $this->recipient = $email_data['recipient'];
        } else {
            $this->recipient = NULL;
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $destinations = ['admin', 'members', 'applicant'];

        if (in_array($this->destination, $destinations)) {

            if ($this->recipient) {
                $this->to($this->recipient);
            }
            else {
                $this->to(config('kwartzlabos.membership_app.'.$this->destination.'.to'));
            }

            $this->subject(config('kwartzlabos.membership_app.'.$this->destination.'.subject').' - '.$this->name);
            if (config('kwartzlabos.membership_app.'.$this->destination.'.cc') != null) {
                $this->cc(config('kwartzlabos.membership_app.'.$this->destination.'.cc'));
            }
            if (config('kwartzlabos.membership_app.'.$this->destination.'.replyto') != null) {
                $this->replyto(config('kwartzlabos.membership_app.'.$this->destination.'.replyto'));
            }
        }

        // only send the message if there is a valid email address from the config, otherwise skip
        if (count($this->to) > 0) {
            return $this->view('emails.memberapp')
                        ->text('emails.memberapp_plain');
        }
    }
}
