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

use Illuminate\Http\Request as HttpRequest;
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
        'profile_id',
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

    protected $appends = [
        'name'
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
     * Get the associated profile model
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongTo
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
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
     * Get the associated requests
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests()
    {
        return $this->hasMany(Request::class, 'requestor_id');
    }
    
    public function log(HttpRequest $request, $action)
    {
        $log = new EmployeeLog();

        $log->action = $action;
        $log->timestamp = date('Y-m-d H:i:s');
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');

        $this->logs()->save($log);
    }

    public function getLeavesBalanceAttribute()
    {
        // TODO: computation
        return $this->profile->allocated_days;
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
        return redirect()->route('employee.index');
    }

    public function canDo($permissions)
    {
        return $permissions == ['*'] || $permissions == '*';
    }
}
