<?php

namespace Tests\Unit\Mail;

use App\Mail\SlackInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlackInviteTest extends TestCase
{
    use RefreshDatabase;

    public function testItBuildsWithExpectedRecipientAndContent()
    {
        $user = User::factory()->active()->create();

        $mail = new SlackInvite($user);
        $mail->build();

        $mail->assertFrom(config('mail.from.address'));
        $mail->assertTo($user->email);
        $mail->assertSeeInHtml($mail->inviteUrl);
        $mail->assertSeeInHtml($user->get_name('first'));
        $mail->assertSeeInText($mail->inviteUrl);
        $mail->assertSeeInText($user->get_name('first'));
    }
}
