<?php

namespace App\Notifications;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

use Illuminate\Bus\Queueable;use Illuminate\Contracts\Queue\ShouldQueue;use Illuminate\Notifications\Notification;

class MobileNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // take params according to your requiremnt.
    public function __construct(
        public ?string $title = null,
        public ?string $body = null,
        public $data = null,
    ) {
    }

    public function via()
    {
        return [FcmChannel::class];
    }


 public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
                title: $this->title,
                body: $this->body
             )))
            ->data($this->data);
    }


    public function toData() {
        return $this->data;
    }
}
