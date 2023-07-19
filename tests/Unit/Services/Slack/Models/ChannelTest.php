<?php

namespace Tests\Unit\Services\Slack\Models;

use App\Services\Slack\Models\Channel;
use Tests\TestCase;

class ChannelTest extends TestCase
{
    public function testItCanBeConstructedWithAnId()
    {
        $channel = new Channel('GeraltOfRivia');
        $this->assertEquals('GeraltOfRivia', $channel->getId());
    }
}