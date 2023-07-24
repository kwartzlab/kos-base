<?php

namespace App\Services\Slack;

use App\Services\Slack\Models\Channel;
use App\Services\Slack\Models\Message;

interface SlackInterface
{
    public function postMessageToChannel(Message $message, Channel $channel);
}