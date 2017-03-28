<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Forms;

use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Extensions\Constraints as CustomAssert;
use Illuminate\Support\Collection;
use App\Models\LeaveType;
use App\Models\User;

class EditLeaveBalanceForm extends Form
{
    protected $model;

    protected $leaveTypes;

    public function __construct(User $model, Collection $leaveTypes, array $options = [])
    {
        $this->model = $model;
        $this->leaveTypes = $leaveTypes;

        parent::__construct(null, $options);
    }

    public function create()
    {
        $this->leaveTypes->each(function (LeaveType $leaveType) {
            $value = 0;

            if ($balance = $this->model->leaveBalances->where('leave_type_id', $leaveType->id)->first()) {
                $value = $balance->entitlement;
            }

            $this->add('leave_' . $leaveType->id, Type\NumberType::class, [
                'data'            => $value,
                'property_path'   => '[leave][' . $leaveType->id . ']'
            ]);
        });
    }
}
