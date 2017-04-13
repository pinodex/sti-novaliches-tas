<?php

/*
 * This file is part of the TAS system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Traits;

use DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * Gives a model method to search by id or name
 */
trait SearchableName
{
    /**
     * @var array Name concatenation variants
     */
    private static $nameConcats = [
        "CONCAT(last_name, ' ', first_name, ' ', middle_name)",
        "CONCAT(last_name, ', ', first_name, ' ', middle_name)",
        "CONCAT(first_name, ' ', middle_name, ' ', last_name)",
        "CONCAT(first_name, ' ', last_name)"
    ];

    /**
     * Search
     * 
     * @param string $column Search queries
     * @param array $relations Relations to include to search
     */
    public static function searchName($model, $keyword)
    {
        $model->where(function (Builder $builder) use ($keyword) {
            foreach (self::$nameConcats as $concat) {
                $builder->orWhere(DB::raw($concat), 'LIKE', '%' . $keyword . '%');
            }
        });

        return $model;
    }
}
