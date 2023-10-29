<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'slack' => [
        'oauth_token' => env('SLACK_OAUTH_TOKEN'),
        'channels' => [
            'applicants' => env('SLACK_APPLICANTS_CHANNEL_ID'),
        ],
        'auto_invite' => [
            'enabled' => env('SLACK_AUTO_INVITE_ENABLED', false),
            'url' => env('SLACK_INVITE_URL'),
        ],
    ],

    'mailman' => [
        'auto_add_enabled' => env('MAILING_LIST_AUTO_INVITE_ENABLED', false),
        'announce' => [
            'request_address' => env('ANNOUNCE_MAILING_LIST_REQUEST_ADDRESS'),
            'password' => env('ANNOUNCE_MAILING_LIST_PASSWORD'),
        ],
        'members' => [
            'request_address' => env('MEMBERS_MAILING_LIST_REQUEST_ADDRESS'),
            'password' => env('MEMBERS_MAILING_LIST_PASSWORD'),
        ],
    ],
];
