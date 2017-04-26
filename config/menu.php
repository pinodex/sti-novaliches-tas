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
                'route'         => 'dashboard.index',
                'name'          => 'Home'
            ],

            [
                'icon'          => 'envelope',
                'identifier'    => 'requests',
                'route'         => 'dashboard.requests.index',
                'name'          => 'Requests',
                'visibility'    => [ Acl::MANAGE_REQUESTS, Acl::SUBMIT_REQUESTS ],
            ],

            [
                'icon'          => 'sticky-note-o',
                'identifier'    => 'bulletin',
                'route'         => 'dashboard.bulletins.index',
                'name'          => 'Bulletin',
                'visibility'    => [ Acl::MANAGE_BULLETIN ]
            ],
        ]
    ],

    [
        'header' => 'Employee',
            
        'list' => [
            [
                'icon'          => 'envelope',
                'identifier'    => 'requests',
                'route'         => 'employee.requests.index',
                'name'          => 'Requests',
            ],

            [
                'icon'          => 'inbox',
                'identifier'    => 'inbox',
                'route'         => 'employee.requests.inbox.index',
                'name'          => 'Request Inbox',
                'visibility'    => function () {
                    if (Auth::user() instanceof Employee) {
                        return Auth::user()->headedDepartment !== null;
                    }

                    return false;
                }
            ],
        ],
    ],

    [
        'header'        => 'Administrator',
        'visibility'    => [ Acl::MANAGE_USERS, Acl::MANAGE_GROUPS, Acl::MANAGE_DEPARTMENTS ],

        'list' => [
            [
                'icon'          => 'user',
                'identifier'    => 'users',
                'route'         => 'dashboard.users.index',
                'name'          => 'Users',
                'visibility'    => [ Acl::MANAGE_USERS ]
            ],

            [
                'icon'          => 'users',
                'identifier'    => 'groups',
                'route'         => 'dashboard.groups.index',
                'name'          => 'Groups',
                'visibility'    => [ Acl::MANAGE_GROUPS ]
            ],

            [
                'icon'          => 'building',
                'identifier'    => 'departments',
                'route'         => 'dashboard.departments.index',
                'name'          => 'Departments',
                'visibility'    => [ Acl::MANAGE_DEPARTMENTS ]
            ],

            [
                'icon'          => 'address-card',
                'identifier'    => 'profiles',
                'route'         => 'dashboard.profiles.index',
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
