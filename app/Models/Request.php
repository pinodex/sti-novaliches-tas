<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
	protected $fillable = [
        'requestor_id', 'approver_id', 'type_id', 'from_date', 'to_date', 'days', 'reason', 'is_approved'
    ];

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

    public function requestor()
    {
    	return $this->belongsTo(User::class, 'requestor_id');
    }

    public function approver()
    {
    	return $this->belongsTo(User::class, 'approver_id');
    }

    public function type()
    {
    	return $this->belongsTo(LeaveType::class, 'type_id');
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
