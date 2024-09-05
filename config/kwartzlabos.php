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

    'membership_coordinator' => [
        'name' => 'Laura Schuhbauer',
    ],
    'membership_app' => [                       // membership app email configuration for admin and members mailings
        'admin' => [
            'to' => 'bod@kwartzlab.ca',
            'cc' => null,
            'replyto' => null,
            'subject' => 'New Member App [BoD Version]',
        ],

        'members' => [
            'to' => 'members@kwartzlab.ca',
            'cc' => null,
            'replyto' => 'members@kwartzlab.ca',
            'subject' => 'New Member Application',
        ],

    ],
    'special_forms' => [
        'new_user_app' => [
            'name' => 'New Member Application',
        ],
        'helpdesk' => [
            'name' => 'Helpdesk Form',
        ],
    ],
    'user_status' => [
        'active' => [
            'name' => 'Active',
            'icon' => 'fa-user-check',
            'colour' => 'success',
            'send-notifications' => true,
        ],
        'hiatus' => [
            'name' => 'On Hiatus',
            'icon' => 'fa-umbrella-beach',
            'colour' => 'warning',
            'send-notifications' => true,
        ],
        'suspended' => [
            'name' => 'Suspended',
            'icon' => 'fa-user-lock',
            'colour' => 'danger',
            'send-notifications' => true,
        ],
        'inactive' => [
            'name' => 'Withdrawn',
            'icon' => 'fa-user-slash',
            'colour' => 'danger',
            'send-notifications' => true,
        ],
        'inactive-abandoned' => [
            'name' => 'Withdrawn [Abandoned]',
            'icon' => 'fa-user-slash',
            'colour' => 'danger',
            'send-notifications' => true,
        ],
        'terminated' => [
            'name' => 'Withdrawn [Terminated]',
            'icon' => 'fa-user-slash',
            'colour' => 'danger',
            'send-notifications' => false,
        ],
        'applicant' => [
            'name' => 'Applicant',
            'icon' => 'fa-user-edit',
            'colour' => 'warning',
            'send-notifications' => false,
        ],
        'applicant-abandoned' => [
            'name' => 'Applicant [Abandoned]',
            'icon' => 'fa-user-times',
            'colour' => 'warning',
            'send-notifications' => false,
        ],
        'applicant-denied' => [
            'name' => 'Applicant [Denied]',
            'icon' => 'fa-user-minus',
            'colour' => 'danger',
            'send-notifications' => false,
        ],
    ],
    'team_roles' => [
        'lead' => [
            'name' => 'Team Lead',
            'plural_name' => 'Team Leads',
            'is_admin' => true,
            'is_trainer' => false,
            'is_maintainer' => false,
            'approval_required' => false,
        ],
        'trainer' => [
            'name' => 'Trainer',
            'plural_name' => 'Trainers',
            'is_admin' => false,
            'is_trainer' => true,
            'is_maintainer' => false,
            'approval_required' => true,
        ],
        'maintainer' => [
            'name' => 'Maintainer',
            'plural_name' => 'Maintainers',
            'is_admin' => false,
            'is_trainer' => false,
            'is_maintainer' => true,
            'approval_required' => false,
        ],
        'consumables' => [
            'name' => 'Consumables',
            'plural_name' => 'Consumables',
            'is_admin' => false,
            'is_trainer' => false,
            'is_maintainer' => false,
            'approval_required' => false,
        ],
        'sme' => [
            'name' => 'Subject Matter Expert',
            'plural_name' => 'Subject Matter Experts',
            'is_admin' => false,
            'is_trainer' => false,
            'is_maintainer' => false,
            'approval_required' => false,
        ],
        'documentation' => [
            'name' => 'Documentation',
            'plural_name' => 'Documentation',
            'is_admin' => false,
            'is_trainer' => false,
            'is_maintainer' => false,
            'approval_required' => false,
        ],
        'communications' => [
            'name' => 'Communications',
            'plural_name' => 'Communications',
            'is_admin' => false,
            'is_trainer' => false,
            'is_maintainer' => false,
            'approval_required' => false,
        ],

    ],
    'team_requests' => [
        'training' => [
            'name' => 'Training',
            'notify' => ['lead', 'trainer'],
        ],
        'maintenance' => [
            'name' => 'Maintenance',
            'notify' => ['lead', 'maintenance'],
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
    'user_flags' => [
        'keys_disabled' => 'Keys Disabled',
        'covid_vaccine' => 'Vaccinated/Exempt',
    ],
    'results_per_page' => [
        'default' => 50,
    ],

];
