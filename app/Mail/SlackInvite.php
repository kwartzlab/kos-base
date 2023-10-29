<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;

class SlackInvite extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public string $inviteUrl;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->inviteUrl = route('slack.invite', ['t' => Crypt::encrypt($user->id)]);
    }

    public function build()
    {
        return $this->to($this->user->email, $this->user->get_name())
            ->from(config('mail.from.address'), config('mail.from.to'))
            ->subject('Kwartzlab Slack Invite')
            ->view('emails.slack_invite', ['name' => $this->user->get_name('first')])
            ->text('emails.slack_invite_text', ['name' => $this->user->get_name('first')]);
    }
}
