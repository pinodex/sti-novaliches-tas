<?php

return [
    'providers' => [
        'user'      => App\Components\MultiAuth\Providers\UserProvider::class,
        'employee'  => App\Components\MultiAuth\Providers\EmployeeProvider::class
    ]
];
