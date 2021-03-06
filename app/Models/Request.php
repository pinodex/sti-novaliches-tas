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
use App\Notifications\RequestApproved;
use App\Notifications\RequestDisapproved;
use App\Notifications\RequestEscalated;
use App\Notifications\RequestReceivedFromEscalation;
use Symfony\Component\HttpFoundation\ParameterBag;
use App\Traits\UuidKey;

class Request extends Model
{
    use UuidKey;

    const STATUS_WAITING = -1;

    const STATUS_DISAPPROVED = 0;

    const STATUS_APPROVED = 1;

    const STATUS_ESCALATED = 5;

    const TYPE_HALF_DAY = 5;

    const TYPE_FULL_DAY = 10;

    public static $statusLabels = [
        self::STATUS_WAITING        => 'Pending',
        self::STATUS_APPROVED       => 'Approved',
        self::STATUS_DISAPPROVED    => 'Disapproved',
        self::STATUS_ESCALATED      => 'Escalated'
    ];

    public static $typeLabels = [
        self::TYPE_FULL_DAY => 'Full Day',
        self::TYPE_HALF_DAY => 'Half Day'
    ];

    protected $fillable = [
        'requestor_id',
        'approver_id',
        'type',
        'subtype',
        'from_date',
        'to_date',
        'incurred_balance',
        'reason',
        'disapproval_reason',
        'status'
    ];

    public $incrementing = false;

    /**
     * Filter results
     * 
     * @param \Symfony\Component\HttpFoundation\ParameterBag $query Query set
     * @param boolean &$isFiltered Is the result filtered
     * 
     * @return Builder
     */
    public static function filter(ParameterBag $query, &$isFiltered = null)
    {
        $requests = static::query();
        
        $requests->when($query->has('status'), function ($builder) use ($query, &$isFiltered) {
            if ($query->get('status') === null) {
                return;
            }

            $builder->where('status', $query->get('status'));

            $isFiltered = true;
        });
        
        $requests->when($query->get('type'), function ($builder) use ($query, &$isFiltered) {
            $builder->where('type', $query->get('type'));

            $isFiltered = true;
        });

        $requests->when($query->get('requestor'), function ($builder) use ($query, &$isFiltered) {
            $ids = User::searchName(null, $query->get('requestor'))->pluck('id');

            $builder->whereIn('requestor_id', $ids);

            $isFiltered = true;
        });

        $requests->when($query->get('approver'), function ($builder) use ($query, &$isFiltered) {
            $ids = User::searchName(null, $query->get('approver'))->pluck('id');

            $builder->whereIn('approver_id', $ids);

            $isFiltered = true;
        });

        $requests->when($query->get('date_filed_from') && $query->get('date_filed_to'),
            function ($builder) use ($query, &$isFiltered) {    
                $builder->whereBetween('created_at', [$query->get('date_filed_from'), $query->get('date_filed_to')]);

                $isFiltered = true;
            }
        );

        $requests->when($query->get('date_requested_from'), function ($builder) use ($query, &$isFiltered) {
            $builder->whereDate('from_date', '>=', $query->get('date_requested_from'));

            $isFiltered = true;
        });

        $requests->when($query->get('date_requested_to'), function ($builder) use ($query, &$isFiltered) {
            $builder->whereDate('to_date', '<=', $query->get('date_requested_to'));

            $isFiltered = true;
        });

        return $requests;
    }

    public function canBeViewedBy(User $user)
    {
        return $user->id == $this->approver_id || $user->id == $this->requestor_id;
    }

    /**
     * Disapproves the request
     * 
     * @param string $reason Disapproval reason
     * 
     * @return boolean
     */
    public function disapprove($reason)
    {
        $this->status = static::STATUS_DISAPPROVED;
        $this->disapproval_reason = $reason;
        $this->responded_at = now();
        
        $this->save();

        $this->requestor->notify(new RequestDisapproved($this));

        return true;
    }

    /**
     * Approve request
     * 
     * @return boolean|\App\Models\Employee
     */
    public function approve()
    {
        // If the approver has department head, escalate the request to department head
        if ($this->approver && $this->approver->department && $this->approver->department->head) {
            $this->approver_id = $this->approver->department->head->id;
            $this->status = static::STATUS_ESCALATED;
            $this->responded_at = now();

            $this->save();

            $this->requestor->notify(new RequestEscalated($this));
            $this->approver->department->head->notify(new RequestReceivedFromEscalation($this));

            return $this->approver->department->head;
        }

        if ($this->approver && !$this->approver->department) {
            $this->status = static::STATUS_APPROVED;
            $this->responded_at = now();
            
            $this->save();

            $this->requestor->notify(new RequestApproved($this));

            return true;
        }

        return false;
    }

    /**
     * Get request creator
     */
    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    /**
     * Get request approver
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function getStatusLabelAttribute()
    {
        $status = 'Unknown';
        $value = $this->attributes['status'];

        if (array_key_exists($value, static::$statusLabels)) {
            $status = static::$statusLabels[$value];
        }

        if ($this->attributes['responded_at'] == null) {
            return $status;
        }

        return sprintf('%s (%s)', 
            $status, date('M d, Y h:i A', strtotime($this->attributes['responded_at']))
        );
    }

    public function getTypeNameAttribute()
    {
        $types = config('request.types');

        if (array_key_exists($this->type, $types)) {
            return $types[$this->type]::getName();
        }

        return 'Unknown';
    }
}
