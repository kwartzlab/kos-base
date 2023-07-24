<?php

namespace Tests\Unit\Services\Slack;

use App\Models\User;
use App\Services\Slack\KosBot;
use App\Services\Slack\Models\Channel;
use App\Services\Slack\Models\Message;
use App\Services\Slack\Slack;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class KosBotTest extends TestCase
{
    private MockObject|Slack $slack;
    private KosBot $kosBot;

    public function setUp(): void
    {
        parent::setUp();
        $this->slack = $this->createMock(Slack::class);
        $this->kosBot = new KosBot($this->slack);
    }

    public function testItPostsANewApplicantMessageToTheApplicantsSlackChannel(): void
    {
        config()->set('services.slack.channels.applicants', '0987654321');

        Carbon::setTestNow(now());
        $formattedDateString = now()->toFormattedDateString();

        /** @var User $user */
        $user = User::factory()->create();

        $this->slack->expects($this->once())
            ->method('postMessageToChannel')
            ->with(
                $this->callback(function (Message $message) use ($formattedDateString, $user) {
                    return is_null($message->getId())
                        && $message->getText() === "{$user->get_name()} - applied {$formattedDateString}";
                }),
                $this->callback(function (Channel $channel) {
                    return $channel->getId() === '0987654321';
                }),
            )
            ->willReturn($this->slack);

        $this->kosBot->postNewAppplicantMessage($user);

        Carbon::setTestNow();
    }
}
