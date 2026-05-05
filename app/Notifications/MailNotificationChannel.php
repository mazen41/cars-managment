<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class MailNotificationChannel extends Notification
{
    public function send($notifiable, $notification)
    {
        $message = $notification->toMail($notifiable);


            \Mail::to($notifiable->email)->queue($message);
    }

}
