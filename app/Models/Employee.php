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

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Components\MultiAuth\AbstractUser;
use App\Traits\PasswordHashable;
use App\Traits\SearchableName;
use App\Traits\WithPicture;

class Employee extends AbstractUser
{
    use SoftDeletes,
        PasswordHashable,
        SearchableName,
        WithPicture;

    const TYPE_FULL_TIME = 'full_time';

    const TYPE_PART_TIME = 'part_time';

    public static $types = [
        'Full Time' => self::TYPE_FULL_TIME,
        'Part Time' => self::TYPE_PART_TIME
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'department_id',
        'username',
        'password',
        'first_name',
        'middle_name',
        'last_name',
        'email_address',
        'type',
        'picture_path',
        'thumbnail_path'
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
     * Get full name from name components
     * 
     * @return string
     */
    public function getNameAttribute()
    {
        return sprintf('%s, %s',
            $this->last_name,
            $this->first_name
        );
    }

    /**
     * Get the associated department models
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the associated employee logs
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(EmployeeLog::class);
    }

    /**
     * Log action for employee
     * 
     * @param \Illuminate\Http\Request $request Request object
     * @param string $action Action code
     */
    public function log(Request $request, $action)
    {
        $log = new EmployeeLog();

        $log->action = $action;
        $log->timestamp = date('Y-m-d H:i:s');
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');

        $this->logs()->save($log);
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return 'employee:' . $this->attributes['id'];
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
        return false;
    }
}
