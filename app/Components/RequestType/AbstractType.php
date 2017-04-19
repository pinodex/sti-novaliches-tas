<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\RequestType;

use DateTime;
use Symfony\Component\Form\Form as SymfonyForm;
use Illuminate\Http\Request;
use App\Http\Forms\Form;
use App\Models\Employee;

abstract class AbstractType
{
    /**
     * @var \App\Http\Forms\Form
     */
    protected $form;

    /**
     * @var \App\Models\Employee
     */
    protected $requestor;

    protected $timeChoices = [
        '9:00 AM',
        '9:30 AM',
        '10:00 AM',
        '10:30 AM',
        '11:00 AM',
        '11:30 AM',
        '12:00 PM',
        '12:30 PM',
        '1:00 PM',
        '1:30 PM',
        '2:00 PM',
        '2:30 PM',
        '3:00 PM',
        '3:30 PM',
        '4:00 PM',
        '4:30 PM',
        '5:00 PM'
    ];

    /**
     * @param \App\Models\Employee $requestor Requestor employee model instance
     */
    public function __construct(Employee $requestor)
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
     * Get form template
     * 
     * @return string
     */
    abstract public function getFormTemplate();

    public function handleRequest(Request $request)
    {
        $this->form->handleRequest($request);

        if ($request->method() == 'POST') {
            return $this->onSubmitted($this->form);
        }
    }

    /**
     * Get approver for requestor
     * 
     * @return \App\Models\Employee
     */
    protected function getApprover()
    {
        if ($this->requestor->department && $this->requestor->department->head) {
            if ($this->requestor->id != $this->requestor->department->head->id) {
                return $this->requestor->department->head;
            }
        }   
    }

    /**
     * Compute days inccured
     * 
     * @param string $fromDate Starting date
     * @param string $toDate Ending date
     * 
     * @return int
     */
    protected function computeDays($fromDate, $toDate)
    {
        $from = date_parse($fromDate);
        $to = date_parse($toDate);

        $days = $to['day'] - $from['day'];
        $fromHour = $from['hour'] + ($from['minute'] / 6 / 10);
        $toHour = $to['hour'] + ($to['minute'] / 6 / 10);

        if ($toHour - $fromHour < 4) {
            $days += 0.5;
        }

        if ($toHour - $fromHour >= 4) {
            $days += 1;
        }

        return $days;
    }

    /**
     * Get 24h time for input time
     * 
     * @param string $time Input time
     * 
     * @return double
     */
    protected function toHour($time)
    {
        $parsed = date_parse($time);
        $hours = $parsed['hour'];

        if ($parsed['minute']) {
            $hours += $parsed['minute'] / 6 / 10;
        }

        return round($hours, 3);
    }

    /**
     * Get the 12h time for input hour
     * 
     * @param double $hour Input hour
     * 
     * @return string
     */
    protected function toTime($hour)
    {
        $minute = round(fmod($hour, 1) * 6 * 10);
        $hour = intval($hour);

        $militaryTime = sprintf('%02d:%02d', $hour, $minute);

        return date('g:i A', strtotime($militaryTime));
    }

    /**
     * Get formatted datetime string for database storage
     * 
     * @param string $datetime Datetime string
     * 
     * @return string
     */
    protected function getFormatted($date, $time = null)
    {
        $dateTimeString = $date;

        if ($time) {
            $dateTimeString .= ' ' . $time;
        }

        $dt = new DateTime($dateTimeString);

        return $dt->format('Y-m-d H:i:s');
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
     * Build the form by adding fields to it
     */
    abstract protected function buildForm();

    /**
     * Called on form post request
     * 
     * @return mixed
     */
    abstract protected function onSubmitted(SymfonyForm $form);
}
