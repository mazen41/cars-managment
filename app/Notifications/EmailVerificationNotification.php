<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\MailNotificationChannel;
use App\Mail\NotificationMailManager;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;


    public function __construct()
    {

    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $notifiable->verification_code = encrypt($notifiable->id);
        $notifiable->save();

        $array['subject'] = translate('Email Verification');
        $array['content'] = translate('Please click the button below to verify your email address.');
        $array['link'] = route('email.verification.confirmation', $notifiable->verification_code);

        return (new MailMessage)
            ->subject($array['subject'])
            ->greeting(translate('Hello') .' '. $notifiable->name . '!')
            ->line($array['content'])
            ->action(translate('Verify Email'), $array['link'])
            ->line(translate('If you did not create an account, no further action is required.'));
    }

    public function toArray($notifiable)
    {
    }
}
