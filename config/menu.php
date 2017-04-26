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
                'route'         => 'admin.index',
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
            ],

            [
                'icon'          => 'inbox',
                'identifier'    => 'inbox',
                'route'         => 'employee.requests.inbox.index',
                'name'          => 'Request Inbox'
            ],
        ],
    ],

    [
        'header'        => 'Administrator',
        'visibility'    => [ Acl::MANAGE_USERS, Acl::MANAGE_GROUPS, Acl::MANAGE_DEPARTMENTS ],

        'list' => [
            [
                'icon'          => 'sticky-note-o',
                'identifier'    => 'bulletin',
                'route'         => 'admin.bulletins.index',
                'name'          => 'Bulletin',
                'visibility'    => [ Acl::MANAGE_BULLETIN ]
            ],

            [
                'icon'          => 'envelope',
                'identifier'    => 'requests',
                'route'         => 'admin.requests.index',
                'name'          => 'Requests',
                'visibility'    => [ Acl::MANAGE_REQUESTS, Acl::SUBMIT_REQUESTS ],
            ],

            [
                'icon'          => 'user',
                'identifier'    => 'users',
                'route'         => 'admin.users.index',
                'name'          => 'Users',
                'visibility'    => [ Acl::MANAGE_USERS ]
            ],

            [
                'icon'          => 'users',
                'identifier'    => 'groups',
                'route'         => 'admin.groups.index',
                'name'          => 'Groups',
                'visibility'    => [ Acl::MANAGE_GROUPS ]
            ],

            [
                'icon'          => 'building',
                'identifier'    => 'departments',
                'route'         => 'admin.departments.index',
                'name'          => 'Departments',
                'visibility'    => [ Acl::MANAGE_DEPARTMENTS ]
            ],

            [
                'icon'          => 'address-card',
                'identifier'    => 'profiles',
                'route'         => 'admin.profiles.index',
                'name'          => 'Profiles',
                'visibility'    => [ Acl::MANAGE_PROFILES ],
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
