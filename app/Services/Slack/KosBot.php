<?php

namespace App\Services\Slack;

use App\Models\User;
use App\Services\Slack\Models\Channel;
use App\Services\Slack\Models\Message;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class KosBot
{
    private Slack $slack;

    public function __construct(Slack $slack)
    {
        $this->slack = $slack;
    }

    public function postNewAppplicantMessage(User $user)
    {
        $formattedDateString = now()->toFormattedDateString();
        return $this->slack->postMessageToChannel(
            (new Message(null))->setText("{$user->get_name()} - applied {$formattedDateString}"),
            new Channel(config('services.slack.channels.applicants'))
        );
    }
}