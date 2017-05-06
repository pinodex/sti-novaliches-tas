<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserImportCompleted extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['database'];
    }

    protected function getLink()
    {
        return route('admin.users.index');
    }

    protected function getIcon()
    {
        return 'upload';
    }

    protected function getContent()
    {
        return 'User import completed';
    }
}
