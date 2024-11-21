<?php

namespace Tests\Unit\Services\Slack\Models;

use App\Services\Slack\Models\Channel;
use Tests\TestCase;

class ChannelTest extends TestCase
{
    public function test_it_can_be_constructed_with_an_id(): void
    {
        $channel = new Channel('GeraltOfRivia');
        $this->assertEquals('GeraltOfRivia', $channel->getId());
    }
}
