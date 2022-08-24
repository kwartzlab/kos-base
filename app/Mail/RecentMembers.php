<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecentMembers extends Mailable
{
    use Queueable, SerializesModels;

    public $month_reported;

    public $member_list;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email_data)
    {
        //
        $this->month_reported = $email_data['month_reported'];
        $this->member_list = $email_data['member_list'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->to(config('kwartzlabos.membership_app.admin.to'));
        $this->subject('Active member visits for '.$this->month_reported);
        if (config('kwartzlabos.membership_app.admin.cc') != null) {
            $this->cc(config('kwartzlabos.membership_app.admin.cc'));
        }
        if (config('kwartzlabos.membership_app.admin.replyto') != null) {
            $this->replyto(config('kwartzlabos.membership_app.admin.replyto'));
        }

        if (count($this->to) > 0) {
            return $this->view('emails.recentmembers');
        }
    }
}
