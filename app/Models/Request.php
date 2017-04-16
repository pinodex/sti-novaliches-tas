<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'requestor_id', 'approver_id', 'type_id', 'from_date', 'to_date', 'days', 'reason', 'is_approved'
    ];

    /**
     * Get request creator
     */
    public function requestor()
    {
        return $this->belongsTo(Employee::class, 'requestor_id');
    }

    /**
     * Get request approver
     */
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    /**
     * Get request type
     */
    public function type()
    {
        return $this->belongsTo(RequestType::class, 'type_id');
    }

    public function getStatusAttribute($value)
    {
        if ($value === false) {
            return 'Denied';
        }

        if ($value === true) {
            return 'Approved';
        }

        return 'Waiting';
    }

    /**
     * Get requests for specified approver
     *
     * @param \App\Models\User $user Approver
     * @return \Illuminate\Pagination\LengthAwarePaginator Paginated result
     */
    public static function getForApprover(User $user)
    {
        if ($user->isInGlobalDepartment()) {
            return self::paginate(50);
        }

        return self::where('approver_id', $user->id)->paginate(50);
    }
}
