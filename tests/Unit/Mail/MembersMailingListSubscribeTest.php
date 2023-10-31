<?php

namespace Tests\Unit\Mail;

use App\Mail\AnnounceMailingListSubscribe;
use App\Mail\MailingListSubscribe;
use App\Mail\MembersMailingListSubscribe;
use App\Models\User;

class MembersMailingListSubscribeTest extends MailingListSubscribeTest
{
    protected function getMailable(User $user): MailingListSubscribe
    {
        return new MembersMailingListSubscribe($user);
    }

    protected function getToAddressConfigPath(): string
    {
        return 'services.mailman.members.request_address';
    }

    protected function getPasswordConfigPath(): string
    {
        return 'services.mailman.members.password';
    }
}