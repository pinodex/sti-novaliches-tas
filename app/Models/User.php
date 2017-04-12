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

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\PasswordHashable;
use App\Extensions\Acl;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes, PasswordHashable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id', 'name', 'username', 'email', 'password'
    ];

    /**
     * Get user group
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get user departments
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'users_departments', 'department_id', 'user_id');
    }

    /**
     * Get headed department
     */
    public function department()
    {
        return $this->hasOne(Department::class, 'head_id');
    }

    /**
     * Get user requests
     */
    public function requests()
    {
        return $this->hasMany(Request::class, 'requestor_id');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value) {}

    public function getRememberTokenName()
    {
        return null;
    }

    public function setAttribute($key, $value)
    {
        $isRememberTokenAttribute = $key == $this->getRememberTokenName();

        if (!$isRememberTokenAttribute) {
            parent::setAttribute($key, $value);
        }
    }

    /**
     * Check if the department headed by this user is a global department
     * 
     * @return bool
     */
    public function isInGlobalDepartment()
    {
        if ($this->department) {
            return $this->department->is_global;
        }

        return false;
    }

    /**
     * Check if user has granted permissions
     * 
     * @param string $permissions,... Permission name
     * 
     * @return boolean
     */
    public function canDo($permissions)
    {
        return Acl::for($this)->can($permissions);
    }
}
