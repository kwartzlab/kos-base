<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\HttpsProtocol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class HttpsProtocolTest extends TestCase
{
    public function test_it_redirects_to_https_in_production_environment(): void
    {
        config(['app.env' => 'production']);
        $_SERVER['HTTPS'] = false;

        /** @var Request $request */
        $request = app(Request::class);
        $middleware = new HttpsProtocol;

        $this->assertFalse($request->isSecure());

        /** @var RedirectResponse $response */
        $response = $middleware->handle(app(Request::class), function () {});

        $this->assertTrue(
            $response->isRedirect(URL::to('/', [], true)),
            'Failed asserting that non-secure request is redirected in production environment.'
        );
    }

    /** @dataProvider provideNonProductionEnvironments */
    public function test_it_does_not_redirect_to_https_outside_production_environment(string $environment): void
    {
        config(['app.env' => $environment]);
        $_SERVER['HTTPS'] = false;

        /** @var Request $request */
        $request = app(Request::class);
        $middleware = new HttpsProtocol;

        $this->assertFalse($request->isSecure());

        $nextMiddlewareCalled = false;
        $response = $middleware->handle(app(Request::class), function () use (&$nextMiddlewareCalled) {
            $nextMiddlewareCalled = true;
        });

        $this->assertTrue(
            $nextMiddlewareCalled === true && is_null($response),
            "Failed asserting that non-secure request is not redirected in $environment environment."
        );
    }

    public function provideNonProductionEnvironments(): array
    {
        return [
            'local' => ['environment' => 'local'],
            'development' => ['environment' => 'development'],
            'testing' => ['environment' => 'testing'],
        ];
    }
}
