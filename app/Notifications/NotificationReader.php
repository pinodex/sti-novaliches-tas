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

use Illuminate\Notifications\DatabaseNotification;

class NotificationReader
{
    /**
     * @var \Illuminate\Notifications\DatabaseNotification
     */    
    protected $notification;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $content;

    /**
     * @param \Illuminate\Notifications\DatabaseNotification $notification Notification model
     */
    public function __construct(DatabaseNotification $notification)
    {
        $mapping = config('notification.mapping');

        $this->notification = $notification;

        if (array_key_exists($notification->type, $mapping)) {
            $entry = $mapping[$notification->type];

            $this->title = $entry['title'];
            $this->content = __('notification.' . $entry['content'], $this->notification->data);
        }
    }

    /**
     * Get notification title
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get notification content
     * 
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get notification model
     * 
     * @return \Illuminate\Notifications\DatabaseNotification
     */
    public function getNotification()
    {
        return $this->notification;
    }
}
