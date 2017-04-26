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
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\PasswordHashable;
use App\Traits\WithPicture;
use App\Components\Acl;

class User extends Authenticatable
{
    use SoftDeletes,
        PasswordHashable,
        WithPicture,
        Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'department_id',
        'group_id',
        'profile_id',
        'username',
        'password',
        'first_name',
        'middle_name',
        'last_name',
        'email_address',
        'picture_path',
        'thumbnail_path',
        'require_password_change'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
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

    protected $appends = [
        'name'
    ];

    /**
     * Get user group
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the associated department model
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the employee headed department model
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function headedDepartment()
    {
        return $this->hasOne(Department::class, 'head_id');
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
        return $this->hasMany(UserLog::class);
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

    /**
     * Get the associated requests linked with approver
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inbox()
    {
        return $this->hasMany(Request::class, 'approver_id');
    }
    
    /**
     * Log action for employee
     * 
     * @param string $action Action code
     * @param array $params Action parameters
     * @param \Illuminate\Http\Request $request Request object
     * 
     */
    public function log($action, array $params = [], HttpRequest $request = null)
    {
        if ($request == null) {
            $request = request();
        }
        
        $log = new UserLog();

        $log->action = $action;
        $log->params = $params;
        $log->timestamp = date('Y-m-d H:i:s');
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');

        $this->logs()->save($log);
    }

    /**
     * Get leave balance
     * 
     * @return int
     */
    public function getLeavesBalanceAttribute()
    {
        if (!$this->profile) {
            return 0;
        }

        $approvedDays = $this->requests()->where('status', Request::STATUS_APPROVED)->sum('incurred_balance');
        $balance = $this->profile->allocated_days - $approvedDays;

        if ($balance < 0) {
            $balance = 0;
        }

        return $balance;
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

    public function getRequirePasswordChangeAttribute($value)
    {
        return $value == 1;
    }
    
    public function setAttribute($key, $value)
    {
        $isRememberTokenAttribute = $key == $this->getRememberTokenName();

        if (!$isRememberTokenAttribute) {
            parent::setAttribute($key, $value);
        }
    }
}
