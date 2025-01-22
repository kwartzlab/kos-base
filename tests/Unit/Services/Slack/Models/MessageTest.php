<?php

namespace Tests\Unit\Services\Slack\Models;

use App\Services\Slack\Models\Message;
use Tests\TestCase;

class MessageTest extends TestCase
{
    public function test_it_can_be_constructed_with_an_id(): void
    {
        $message = new Message('GeraltOfRivia');
        $this->assertEquals('GeraltOfRivia', $message->getId());
    }

    public function test_it_can_be_constructed_without_an_id(): void
    {
        $message = new Message;
        $this->assertNull($message->getId());
    }

    public function test_it_can_fluently_set_the_text(): void
    {
        $message = new Message;

        $this->assertSame($message, $message->setText('Damn, a storm.'));
        $this->assertEquals('Damn, a storm.', $message->getText());
    }
}
