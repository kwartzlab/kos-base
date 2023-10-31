<?php

namespace App\Mail;

class MembersMailingListSubscribe extends MailingListSubscribe
{
    protected function getToEmail(): string
    {
        return config('services.mailman.members.request_address');
    }

    protected function getPassword(): string
    {
        return config('services.mailman.members.password');
    }
}
