<?php

namespace App\Mail;

class AnnounceMailingListSubscribe extends MailingListSubscribe
{
    protected function getToEmail(): string
    {
        return config('services.mailman.announce.request_address');
    }

    protected function getPassword(): string
    {
        return config('services.mailman.announce.password');
    }
}
