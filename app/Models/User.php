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

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Components\MultiAuth\AbstractUser;
use App\Components\Acl;
use App\Traits\WithPicture;
use App\Traits\PasswordHashable;

class User extends AbstractUser
{
    use SoftDeletes,
        PasswordHashable,
        WithPicture;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id',
        'name',
        'username',
        'email_address',
        'password'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_password_change_at',
        'last_login_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];
    
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return 'user:' . $this->attributes['id'];
    }

    public function getAuthPassword()
    {
        return $this->attributes['password'];
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value) {}

    public function getRememberTokenName()
    {
        return null;
    }

    public function getRedirectAction()
    {
        return redirect()->route('dashboard.index');
    }

    public function canDo($permissions)
    {
        return Acl::for($this)->can($permissions);
    }

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
}
