<?php

namespace Tests\Unit\Mail;

use App\Mail\MailingListSubscribe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class MailingListSubscribeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_with_expected_recipient_and_content()
    {
        config([$this->getToAddressConfigPath() => 'geralt@rivia.of']);
        config([$this->getPasswordConfigPath() => 'w1nd5h0wl1ng']);

        $user = User::factory()->create();

        $mail = $this->getMailable($user);
        $mail->build();

        $mail->assertFrom(config('mail.from.address'));
        $mail->assertTo('geralt@rivia.of');
        $mail->assertSeeInText($user->email);
        $mail->assertSeeInText('w1nd5h0wl1ng');
    }

    abstract protected function getMailable(User $user): MailingListSubscribe;

    abstract protected function getToAddressConfigPath(): string;

    abstract protected function getPasswordConfigPath(): string;
}
