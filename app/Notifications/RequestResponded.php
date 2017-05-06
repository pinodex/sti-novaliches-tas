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
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Request;

class RequestResponded extends Notification implements ShouldQueue
{
    use Queueable;
    
    /**
     * @var \App\Models\Request Request object instance
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mapping = config('notification.mapping');
        $types = config('request.types');
        
        $notifClass = get_class($this);

        $entry = $mapping[$notifClass];

        $message = (new MailMessage)
            ->subject(sprintf('%s: %s', $entry['title'], $this->request->type_name))
            ->markdown('emails.request_responded', [
                'entry'     => $entry,
                'recipient' => $notifiable,
                'request'   => $this->request,
                'url'       => $this->getLink($notifiable)
            ]);

        return $message;
    }

    protected function getLink($notifiable)
    {
        return route('employee.requests.view', [
            'request' => $this->request
        ]);
    }

    protected function getIcon($notifiable)
    {
        return 'envelope';
    }

    protected function getContent($notifiable)
    {
        return 'There has been a response to your request';
    }
}
