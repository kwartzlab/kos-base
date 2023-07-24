<?php

namespace Tests\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

trait MocksGuzzleHistory
{
    /** @var array */
    private $guzzleHistory = [];

    /** @var MockHandler */
    private $guzzleHandler;

    /** @var Client */
    private $guzzleClient;

    public function getGuzzleHistory(): array
    {
        return $this->guzzleHistory;
    }

    public function getGuzzleHandler(): MockHandler
    {
        return $this->guzzleHandler;
    }

    public function getGuzzleClient(): Client
    {
        return $this->guzzleClient;
    }

    protected function mockGuzzleHistory(array &$container, array $config = []): array
    {
        $this->guzzleHandler = new MockHandler();
        $history = Middleware::history($container);
        $stack = HandlerStack::create($this->guzzleHandler);
        $stack->push($history);
        $this->guzzleClient = new Client(array_merge(['handler' => $stack], $config));
        app()->bind(Client::class, function () {
            return $this->guzzleClient;
        });
        return $container;
    }

    protected function appendJsonResponse(int $status, string $json): void
    {
        $this->guzzleHandler->append(new Response($status, [], $json));
    }

    protected function assertGuzzleHistoryContains($url): void
    {
        $this->assertTrue(
            collect($this->guzzleHistory)
                ->contains(function (array $requestContainer) use ($url) {
                    /** @var Request $request */
                    $request = $requestContainer['request'];
                    return $request->getUri()->getScheme()
                        . '://'
                        . $request->getUri()->getHost()
                        . $request->getUri()->getPath()
                        . ($request->getUri()->getQuery() ? '?'. $request->getUri()->getQuery() : '') === $url;
                }),
            "Failed asserting that Guzzle history contains a request to `$url`."
        );
    }
}