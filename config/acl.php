<?php

/*
|--------------------------------------------------------------------------
| KwartzlabOS Access Control List
|--------------------------------------------------------------------------
|
| ACL items which are assignable to user roles
|
| Permissions are defined as object => operation (i.e keys => manage)
|
*/

return [

    'permissions' => [

        'keys' => ['manage'],
        'users' => ['manage'],
        'gatekeepers' => ['manage'],
        'teams' => ['manage'],
        'reports' => ['manage', 'all', 'keys', 'users', 'gatekeepers', 'teams'],
        'roles' => ['manage'],
        'forms' => ['manage'],
    ],

];
