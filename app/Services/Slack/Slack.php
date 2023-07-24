<?php

namespace App\Services\Slack;

use App\Services\Slack\Models\Channel;
use App\Services\Slack\Models\Message;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Slack implements SlackInterface
{
    private string $token;
    private Client $http;

    public function __construct(string $token, Client $http)
    {
        $this->token = $token;
        $this->http = $http;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function postMessageToChannel(Message $message, Channel $channel)
    {
        return $this->http->post('chat.postMessage', [
            RequestOptions::FORM_PARAMS => [
                'token' => $this->token,
                'channel' => $channel->getId(),
                'text' => $message->getText(),
            ],
        ]);
    }
}