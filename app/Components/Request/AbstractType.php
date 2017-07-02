<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\Request;

use DateTime;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Http\Forms\Form;
use App\Models\User;

abstract class AbstractType
{
    /**
     * @var \App\Http\Forms\Form
     */
    protected $form;

    /**
     * @var \App\Models\User
     */
    protected $requestor;

    /**
     * @var array
     */
    protected $timeChoices = [
        '5:00 AM' => '05:00:00',
        '5:30 AM' => '05:30:00',
        '6:00 AM' => '06:00:00',
        '6:30 AM' => '06:30:00',
        '7:00 AM' => '07:00:00',
        '7:30 AM' => '07:30:00',
        '8:00 AM' => '08:00:00',
        '8:30 AM' => '08:30:00',
        '9:00 AM' => '09:00:00',
        '9:30 AM' => '09:30:00',
        '10:00 AM' => '10:00:00',
        '10:30 AM' => '10:30:00',
        '11:00 AM' => '11:00:00',
        '11:30 AM' => '11:30:00',
        '12:00 PM' => '12:00:00',
        '12:30 PM' => '12:30:00',
        '1:00 PM' => '13:00:00',
        '1:30 PM' => '13:30:00',
        '2:00 PM' => '14:00:00',
        '2:30 PM' => '14:30:00',
        '3:00 PM' => '15:00:00',
        '3:30 PM' => '15:30:00',
        '4:00 PM' => '16:00:00',
        '4:30 PM' => '16:30:00',
        '5:00 PM' => '17:00:00',
        '5:30 PM' => '17:30:00',
        '6:00 PM' => '18:00:00',
        '6:30 PM' => '18:30:00',
        '7:00 PM' => '19:00:00',
        '7:30 PM' => '19:30:00',
        '8:00 PM' => '20:00:00',
        '8:30 PM' => '20:30:00',
        '9:00 PM' => '21:00:00',
    ];

    /**
     * @param \App\Models\User $requestor Requestor employee model instance
     */
    public function __construct(User $requestor)
    {
        $this->form = new Form();
        $this->requestor = $requestor;

        $this->buildForm();
        $this->form = $this->form->getForm();
    }

    /**
     * Get form
     * 
     * @return \App\Http\Forms\Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Handle request. Calls form->handleRequest internally
     * 
     * @return mixed
     */
    public function handleRequest(Request $request)
    {
        $this->form->handleRequest($request);

        if ($request->method() == 'POST') {
            return $this->onSubmit($request);
        }
    }

    /**
     * Get approver for requestor
     * 
     * @return \App\Models\User
     */
    public function getApprover()
    {
        if ($this->requestor->department && $this->requestor->department->head) {
            if ($this->requestor->id != $this->requestor->department->head->id) {
                return $this->requestor->department->head;
            }
        }

        throw new RequestException('No assigned approver');
    }

    /**
     * Compute date difference
     * 
     * @param DateTime $fromDate Starting date
     * @param DateTime $toDate Ending date
     * @param int $type Leave type
     * 
     * @return int
     */
    public function diff(DateTime $fromDate, DateTime $toDate, $type = RequestModel::TYPE_FULL_DAY)
    {
        if ($fromDate > $toDate) {
            throw new RequestException('Start date is past the end date');
        }
        
        $diff = $toDate->diff($fromDate);
        $days = $diff->days;

        if ($days == 0) {
            switch ($type) {
                case RequestModel::TYPE_HALF_DAY:
                    return 0.5;

                case RequestModel::TYPE_FULL_DAY:
                    return 1;
            }
        }

        if ($diff->h > 0 && $diff->h <= 4) {
            $days += 0.5;
        }

        if ($diff->h > 4) {
            $days += 1;
        }

        return $days;
    }

    /**
     * Make request model from request data
     * 
     * @param \Illuminate\Http\Request $request Request data
     * 
     * @return \App\Models\Request
     */
    public function makeModel(Request $request)
    {
        $model = new RequestModel();

        $fromDate = new DateTime("{$request->start_date} {$request->start_time}");
        $toDate = new DateTime("{$request->end_date} {$request->end_time}");

        $days = $this->diff($fromDate, $toDate, $request->subtype);

        $model->fill([
            'requestor_id'      => $this->requestor->id,
            'approver_id'       => $this->getApprover()->id,
            'type'              => $this->getMoniker(),
            'subtype'           => $request->subtype,
            
            'from_date'         => $fromDate->format('Y-m-d H:i:s'),
            'to_date'           => $toDate->format('Y-m-d H:i:s'),
            'incurred_balance'  => $days,

            'reason'            => $request->input('reason'),
            'status'            => RequestModel::STATUS_WAITING
        ]);

        return $model;
    }

    /**
     * Get type name
     * 
     * @return string
     */
    abstract public static function getName();

    /**
     * Get type moniker
     * 
     * @return string
     */
    abstract public static function getMoniker();

    /**
     * Get form template
     * 
     * @return string
     */
    abstract public function getFormTemplate();

    /**
     * Build the form by adding fields to it
     */
    abstract protected function buildForm();

    /**
     * Called on form post request
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    abstract protected function onSubmit(Request $request);
}
