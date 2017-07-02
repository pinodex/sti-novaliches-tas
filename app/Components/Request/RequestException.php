<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\Request;

use Exception;

class RequestException extends Exception
{
    public function __construct($message = 'Request error')
    {
        parent::__construct($message);
    }
}
