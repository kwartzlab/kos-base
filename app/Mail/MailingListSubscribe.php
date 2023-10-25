<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class MailingListSubscribe extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build(): MailingListSubscribe
    {
        return $this->to($this->getToEmail())
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject(null)
            ->text('emails.mailing_list_subscribe_text', [
                'email' => $this->user->email,
                'password' => $this->getPassword(),
            ]);
    }

    protected abstract function getToEmail(): string;

    protected abstract function getPassword(): string;
}
