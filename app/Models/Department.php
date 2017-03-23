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

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'head_id'
    ];

    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function users()
    {
    	return $this->belongsToMany(User::class, 'users_departments', 'user_id', 'department_id');
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
