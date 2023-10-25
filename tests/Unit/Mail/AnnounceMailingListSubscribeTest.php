<?php

namespace Tests\Unit\Mail;

use App\Mail\AnnounceMailingListSubscribe;
use App\Mail\MailingListSubscribe;
use App\Models\User;

class AnnounceMailingListSubscribeTest extends MailingListSubscribeTest
{
    protected function getMailable(User $user): MailingListSubscribe
    {
        return new AnnounceMailingListSubscribe($user);
    }

    protected function getToAddressConfigPath(): string
    {
        return 'services.mailman.announce.request_address';
    }

    protected function getPasswordConfigPath(): string
    {
        return 'services.mailman.announce.password';
    }
}