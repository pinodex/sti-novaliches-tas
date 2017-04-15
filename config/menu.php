<?php

use App\Components\Acl;

return [
    'top' => [

    ],

    'side' => [
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

                    'list' => [
                        [
                            'icon'          => 'plus',
                            'identifier'    => 'create',
                            'route'         => 'dashboard.requests.create',
                            'name'          => 'Create Request',
                            'visibility'    => [ Acl::SUBMIT_REQUESTS ]
                        ],

                        [
                            'icon'          => 'user-circle-o',
                            'identifier'    => 'me',
                            'route'         => 'dashboard.requests.me',
                            'name'          => 'My Requests'
                        ]
                    ]
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
            'header'        => 'Administrator',
            'visibility'    => [ Acl::MANAGE_USERS, Acl::MANAGE_GROUPS, Acl::MANAGE_DEPARTMENTS ],

            'list' => [
                [
                    'icon'          => 'user',
                    'identifier'    => 'users',
                    'route'         => 'dashboard.users.index',
                    'name'          => 'Users',
                    'visibility'    => [ Acl::MANAGE_USERS ],
                    
                    'list' => [
                        [
                            'icon'          => 'trash',
                            'identifier'    => 'deleted',
                            'route'         => 'dashboard.users.deleted',
                            'name'          => 'Deleted Users'
                        ]
                    ]
                ],

                [
                    'icon'          => 'users',
                    'identifier'    => 'groups',
                    'route'         => 'dashboard.groups.index',
                    'name'          => 'Groups',
                    'visibility'    => [ Acl::MANAGE_GROUPS ],

                    'list' => [
                        [
                            'icon'          => 'trash',
                            'identifier'    => 'deleted',
                            'route'         => 'dashboard.groups.deleted',
                            'name'          => 'Deleted Groups'
                        ]
                    ]
                ],

                [
                    'icon'          => 'building',
                    'identifier'    => 'departments',
                    'route'         => 'dashboard.departments.index',
                    'name'          => 'Departments',
                    'visibility'    => [ Acl::MANAGE_DEPARTMENTS ],

                    'list' => [
                        [
                            'icon'          => 'trash',
                            'identifier'    => 'deleted',
                            'route'         => 'dashboard.departments.deleted',
                            'name'          => 'Deleted Departments'
                        ]
                    ]
                ],

                [
                    'icon'          => 'address-book',
                    'identifier'    => 'employees',
                    'route'         => 'dashboard.employees.index',
                    'name'          => 'Employees',
                    'visibility'    => [ Acl::MANAGE_EMPLOYEES ],
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
    ]
];
