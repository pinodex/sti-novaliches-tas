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
use Jenssegers\Agent\Agent;

class EmployeeLog extends Model
{
    public $timestamps = false;

    /**
     * @var \Jenssegers\Agent\Agent
     */
    protected $agent;

    /**
     * Get Agent instance for the recorded user agent
     * 
     * @return \Jenssegers\Agent\Agent
     */
    public function getAgent()
    {
        if ($this->agent !== null) {
            return $this->agent;
        }

        $this->agent = new Agent();
        $this->agent->setUserAgent($this->user_agent);

        return $this->agent;
    }

    public function getUserAgentFriendlyName()
    {
        $agent = $this->getAgent();

        return sprintf('%s (%s)', $agent->browser(), $agent->platform());
    }
}
