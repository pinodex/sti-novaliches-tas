<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RequestDisapproved extends RequestResponded
{
    protected function getImage($notifiable)
    {
        if (!$this->request->approver) {
            return default_avatar_thumb();
        }

        return $this->request->approver->picture['thumb'];
    }

    protected function getContent($notifiable)
    {
        if (!$this->request->approver) {
            return 'Your request has been disapproved';
        }

        return 'Your request has been disapproved by ' .
            $this->request->approver->name;
    }
}
