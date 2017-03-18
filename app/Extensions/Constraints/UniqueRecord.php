<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueRecord extends Constraint
{
    /**
     * @var string Model class for record check
     */
    public $model;

    /**
     * @var string Row to check
     */
    public $row;

    /**
     * @var string Comparison operator to use in where clause
     */

    public $comparator = '=';

    /**
     * @var string Value to exclude in check
     */
    public $exclude;

    /**
     * @var string Error message
     */
    public $message = 'Record already exists';
}
