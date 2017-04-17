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
        if ($this->attributes['is_approved'] === 0) {
            return 'Disapproved';
        }

        if ($this->attributes['is_approved'] === 1) {
            return 'Approved';
        }

        if ($this->attributes['is_approved'] === 5) {
            return 'Escalated';
        }

        return 'Waiting';
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
