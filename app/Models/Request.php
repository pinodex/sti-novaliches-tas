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

class Request extends Model
{
    protected $fillable = [
        'requestor_id',
        'approver_id',
        'type_id',
        'from_date',
        'to_date',
        'incurred_balance',
        'reason',
        'disapproval_reason',
        'is_approved'
    ];

    /**
     * Disapproves the request
     * 
     * @param string $reason Disapproval reason
     * 
     * @return boolean
     */
    public function disapprove($reason)
    {
        $this->is_approved = 0;
        $this->disapproval_reason = $reason;
        $this->responded_at = date('Y-m-d H:i:s');
        
        $this->save();

        $this->requestor->notify(new RequestDisapproved($this));

        return true;
    }

    /**
     * Escalate the request to the superior
     * 
     * @param \App\Models\Employee $employee Employee model to pass approval to
     * 
     * @return \App\Models\Employee Passed employee
     */
    public function escalate(Employee $employee = null)
    {
        if ($employee) {
            $this->approver_id = $employee->id;
            $this->is_approved = 5;
            $this->responded_at = date('Y-m-d H:i:s');

            $this->save();

            $this->requestor->notify(new RequestEscalated($this));
            $employee->notify(new RequestReceivedFromEscalation($this));

            return $employee;
        }

        if ($this->approver && $this->approver->department && $this->approver->department->head) {
            $this->approver_id = $this->approver->department->head->id;
            $this->is_approved = 5;
            $this->responded_at = date('Y-m-d H:i:s');

            $this->save();

            $this->requestor->notify(new RequestEscalated($this));
            $this->approver->department->head->notify(new RequestReceivedFromEscalation($this));

            return $this->approver->department->head;
        }
    }

    /**
     * Approve request
     * 
     * @return boolean
     */
    public function approve()
    {
        if ($this->approver && !$this->approver->department) {
            $this->is_approved = true;
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
        return $this->belongsTo(Employee::class, 'requestor_id');
    }

    /**
     * Get request approver
     */
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    public function getStatusAttribute($value)
    {
        $status = 'Waiting';

        if ($this->attributes['is_approved'] === null) {
            return $status;
        }

        if ($this->attributes['is_approved'] === 0) {
            $status = 'Dispproved';
        }

        if ($this->attributes['is_approved'] === 1) {
            $status = 'Approved';
        }

        if ($this->attributes['is_approved'] === 5) {
            $status = 'Escalated';
        }

        if ($this->attributes['responded_at'] == null) {
            return $status;
        }

        return sprintf('%s (%s)', 
            $status, date('M d, Y h:i A', strtotime($this->attributes['responded_at']))
        );
    }

    public function getTypeAttribute($value)
    {
        $types = config('request.types');

        if (array_key_exists($value, $types)) {
            return $types[$value]::getName();
        }

        return 'Unknown';
    }
}
