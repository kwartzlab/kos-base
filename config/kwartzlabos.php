<?php

/*
|--------------------------------------------------------------------------
| KwartzlabOS Configuration Options
|--------------------------------------------------------------------------
|
| Custom options used throughout kOS
|
*/

return [

    'membership_app' => [                       // membership app email configuration for admin and members mailings
        'admin' => [
            'to' => 'membership@kwartzlab.ca',
            'cc' => 'bod@kwartzlab.ca',
            'replyto' => NULL
        ],

        'members' => [
            'to' => 'members@kwartzlab.ca',
            'cc' => NULL,
            'replyto' => 'members@kwartzlab.ca'
        ],

    ],

];


