<?php

namespace Tests\Unit\Services\Slack\Models;

use App\Services\Slack\Models\Channel;
use App\Services\Slack\Models\Message;
use Tests\TestCase;

class MessageTest extends TestCase
{
    public function testItCanBeConstructedWithAnId(): void
    {
        $message = new Message('GeraltOfRivia');
        $this->assertEquals('GeraltOfRivia', $message->getId());
    }

    public function testItCanBeConstructedWithoutAnId(): void
    {
        $message = new Message();
        $this->assertNull($message->getId());
    }

    public function testItCanFluentlySetTheText(): void
    {
        $message = new Message();

        $this->assertSame($message, $message->setText('Damn, a storm.'));
        $this->assertEquals('Damn, a storm.', $message->getText());
    }
}