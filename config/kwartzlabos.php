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
    'org_name' => 'Kwartzlab Makerspace',
    'org_name_short' => 'Kwartzlab',
    'org_logo' => '/storage/images/kwartzlab.png',
    'org_logo_email' => '/storage/images/kwartzlab-logo-email.png',

    'entrance_gatekeepers' => [11],             // which gatekeepers are building entrances (used for metrics)

    'membership_app' => [                       // membership app email configuration for admin and members mailings
        'admin' => [
            'to' => 'membership@kwartzlab.ca',
            'cc' => 'bod@kwartzlab.ca',
            'replyto' => NULL,
            'subject' => 'New Member App [BoD Version]'
        ],

        'members' => [
            'to' => 'members@kwartzlab.ca',
            'cc' => NULL,
            'replyto' => 'members@kwartzlab.ca',
            'subject' => 'New Member Application'
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
            'plural_name' => 'Team Leads',
            'is_admin' => true,
            'is_trainer' => false,
            'is_maintainer' => false,
            'approval_required' => false
        ],
        'trainer' => [
            'name' => 'Trainer',
            'plural_name' => 'Trainers',
            'is_admin' => false,
            'is_trainer' => true,
            'is_maintainer' => false,
            'approval_required' => true
        ],
        'maintainer' => [
            'name' => 'Maintainer',
            'plural_name' => 'Maintainers',
            'is_admin' => false,
            'is_trainer' => false,
            'is_maintainer' => true,
            'approval_required' => false
        ],
        'consumables' => [
            'name' => 'Consumables',
            'plural_name' => 'Consumables',
            'is_admin' => false,
            'is_trainer' => false,
            'is_maintainer' => false,
            'approval_required' => false
        ],
        'sme' => [
            'name' => 'Subject Matter Expert',
            'plural_name' => 'Subject Matter Experts',
            'is_admin' => false,
            'is_trainer' => false,
            'is_maintainer' => false,
            'approval_required' => false
        ],
        'documentation' => [
            'name' => 'Documentation',
            'plural_name' => 'Documentation',
            'is_admin' => false,
            'is_trainer' => false,
            'is_maintainer' => false,
            'approval_required' => false
        ],
        'communications' => [
            'name' => 'Communications',
            'plural_name' => 'Communications',
            'is_admin' => false,
            'is_trainer' => false,
            'is_maintainer' => false,
            'approval_required' => false
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
    'socials' => [
        'twitter' => 'Twitter',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'snapchat' => 'Snapchat',
        'linkedin' => 'LinkedIn',
    ],
    'certifications' => [
        'firstaid' => 'First Aid',
        'healthsafety' => 'Health & Safety',
        'professional' => 'Professional',
        'technical' => 'Technical',
        'other' => 'Other',
    ],
    
];


