<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\Sso\Responses;

class ErrorResponse extends Response
{
    const GENERIC_ERROR = 'GENERIC_ERROR';
    
    const INVALID_CLIENT = 'INVALID_CLIENT';

    const INVALID_CODE = 'INVALID_CODE';

    const EXPIRED_CODE = 'EXPIRED_CODE';
    
    public function __construct($message = 'An error occurred', $code = Response::GENERIC_ERROR, $status = 400, $headers = [], $options = 0)
    {
        $data = [
            'error' => [
                'code'      => $code,
                'status'    => $status,
                'message'   => $message
            ]
        ];

        parent::__construct($data, $status, $headers, $options);
    }
}
