<?php

namespace Tests\Unit\Services\Slack;

use App\Services\Slack\Models\Channel;
use App\Services\Slack\Models\Message;
use App\Services\Slack\Slack;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Traits\MocksGuzzleHistory;

class SlackTest extends TestCase
{
    use MocksGuzzleHistory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockGuzzleHistory($this->guzzleHistory, ['base_uri' => 'https://slack.com/api/']);
    }

    public function test_it_can_be_constructed_with_an_oauth_token(): void
    {
        $slack = new Slack('geralt-of-rivia', $this->getGuzzleClient());
        $this->assertEquals('geralt-of-rivia', $slack->getToken());
    }

    public function test_it_is_constructed_from_the_app_container_with_the_configured_oauth_token(): void
    {
        $originalOauthToken = config('services.slack.oauth_token');
        config()->set('services.slack.oauth_token', 'geralt-of-rivia');

        /** @var Slack $slack */
        $slack = app(Slack::class);

        $this->assertEquals('geralt-of-rivia', $slack->getToken());

        config()->set('services.slack.oauth_token', $originalOauthToken);
    }

    public function test_it_makes_an_http_request_to_post_a_new_message_to_a_channel(): void
    {
        $this->appendJsonResponse(
            Response::HTTP_CREATED,
            json_encode([])
        );

        $slack = new Slack('some-oauth-token', $this->getGuzzleClient());
        $message = (new Message)->setText('Wind\'s howling.');
        $channel = new Channel('CH4NN3L1D');

        $slack->postMessageToChannel($message, $channel);

        $this->assertGuzzleHistoryContains('https://slack.com/api/chat.postMessage');

        parse_str(urldecode($this->getGuzzleHistory()[0]['request']->getBody()->getContents()), $requestFormParams);
        $this->assertEquals('some-oauth-token', $requestFormParams['token']);
        $this->assertEquals('CH4NN3L1D', $requestFormParams['channel']);
        $this->assertEquals('Wind\'s howling.', $requestFormParams['text']);
    }
}
