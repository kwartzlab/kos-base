<?php

namespace App\Providers;

use App\Services\Slack\KosBot;
use App\Services\Slack\Slack;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->singleton(Slack::class, function () {
            return new Slack(
                config('services.slack.oauth_token'),
                new Client(['base_uri' => 'https://slack.com/api/']),
            );
        });

        $this->app->singleton(KosBot::class, function () {
            return new KosBot(app(Slack::class));
        });
    }

    public function register(): void
    {
        //
    }
}
