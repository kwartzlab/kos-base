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
    'special_forms' => [
        'new_user_app' => [
            'name' => 'New Member Application'
        ],
        'helpdesk' => [
            'name' => 'Helpdesk Form'
        ],
    ],
    'user_status' => [
        'active' => 'Active',
        'applicant' => 'Applicant',
        'hiatus' => 'On Hiatus',
        'withdrawn' => 'Withdrawn',
        'suspended' => 'Suspended',
        'abandoned' => 'Applicant [Abandoned]',
        'denied' => 'Applicant [Denied]'
    ],
    'team_roles' => [
        'lead' => [
            'name' => 'Team Lead',
            'is_admin' => true,
            'is_trainer' => true,
            'is_maintainer' => true
        ],
        'trainer' => [
            'name' => 'Trainer',
            'is_admin' => false,
            'is_trainer' => true,
            'is_maintainer' => false
        ],
        'maintainer' => [
            'name' => 'Maintainer',
            'is_admin' => false,
            'is_trainer' => false,
            'is_maintainer' => true
        ],
        'sme' => [
            'name' => 'Subject Expert',
            'is_admin' => false,
            'is_trainer' => false,
            'is_maintainer' => false
        ],

    ],
    'team_requests' => [
        'training' => [
            'name' => 'Training',
            'notify' => ['lead','trainer']
        ],
        'maintenance' => [
            'name' => 'Maintenance',
            'notify' => ['lead','maintenance']
        ],
    ],
    'team_request_status' => [
        'new' => 'New',
        'cancelled' => 'Cancelled',
        'completed' => 'Completed',
        'failed' => 'Did Not Finish',
    ],
    'auth_expiry_types' => [
        'revoke' => 'Revoke Authorization',
        'retrain' => 'Requires Retraining',
    ],
    
];


