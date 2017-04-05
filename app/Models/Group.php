<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'permissions'
    ];

    /**
     * Get group users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getPermissionsAttribute($value)
    {
        if ($value == null) {
            return null;
        }

        return explode(',', $value);
    }

    public function setPermissionsAttribute(array $value)
    {
        $this->attributes['permissions'] = implode(',', $value);
    }
}
