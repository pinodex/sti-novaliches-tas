<?php

return [
    'types' => [
        'vacation_leave' => App\Components\RequestType\VacationLeaveType::class,
        'sick_leave' => App\Components\RequestType\SickLeaveType::class,
        'official_business' => App\Components\RequestType\OfficialBusinessType::class,
        'undertime' => App\Components\RequestType\UndertimeType::class,
        'overtime' => App\Components\RequestType\OvertimeType::class
    ]
];
