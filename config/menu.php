<?php

use App\Components\Acl;
use App\Models\Employee;

return [
    [
        'header' => null,
        
        'list' => [
            [
                'icon'          => 'home',
                'identifier'    => 'home',
                'route'         => 'index',
                'name'          => 'Home'
            ]
        ]
    ],

    [
        'header' => 'Employee',
            
        'list' => [
            [
                'icon'          => 'envelope',
                'identifier'    => 'myrequests',
                'route'         => 'employee.requests.index',
                'name'          => 'My Requests',
                'visibility'    => [ Acl::SUBMIT_REQUESTS ]
            ],

            [
                'icon'          => 'inbox',
                'identifier'    => 'inbox',
                'route'         => 'employee.requests.inbox.index',
                'name'          => 'Request Inbox',
                'visibility'    => [ Acl::APPROVE_DISAPPROVE_REQUESTS ]
            ],
        ],
    ],

    [
        'header'        => 'Administrator',
        'visibility'    => [ Acl::ADMIN_USERS, Acl::ADMIN_GROUPS, Acl::ADMIN_DEPARTMENTS ],

        'list' => [
            [
                'icon'          => 'sticky-note-o',
                'identifier'    => 'bulletin',
                'route'         => 'admin.bulletins.index',
                'name'          => 'Bulletin',
                'visibility'    => [ Acl::ADMIN_BULLETIN ]
            ],

            [
                'icon'          => 'envelope',
                'identifier'    => 'requests',
                'route'         => 'admin.requests.index',
                'name'          => 'Requests',
                'visibility'    => [ Acl::ADMIN_REQUESTS, Acl::SUBMIT_REQUESTS ],
            ],

            [
                'icon'          => 'user',
                'identifier'    => 'users',
                'route'         => 'admin.users.index',
                'name'          => 'Users',
                'visibility'    => [ Acl::ADMIN_USERS ]
            ],

            [
                'icon'          => 'users',
                'identifier'    => 'groups',
                'route'         => 'admin.groups.index',
                'name'          => 'Groups',
                'visibility'    => [ Acl::ADMIN_GROUPS ]
            ],

            [
                'icon'          => 'building',
                'identifier'    => 'departments',
                'route'         => 'admin.departments.index',
                'name'          => 'Departments',
                'visibility'    => [ Acl::ADMIN_DEPARTMENTS ]
            ],

            [
                'icon'          => 'address-card',
                'identifier'    => 'profiles',
                'route'         => 'admin.profiles.index',
                'name'          => 'Profiles',
                'visibility'    => [ Acl::ADMIN_PROFILES ],
            ]
        ]  
    ],

    [
        'header' => 'Account',

        'list' => [
            [
                'icon'          => 'cog',
                'identifier'    => 'settings',
                'route'         => 'account.settings.index',
                'name'          => 'Account Settings'
            ]
        ]  
    ]
];
