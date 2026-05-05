<?php

namespace App\Notifications;

use App\Models\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewCustomerNotification  extends Notification implements ShouldQueue
{

    use Queueable;
    private $data;
    private $notificationType;
    /**
     * Create a new notification instance.
     */
    public function __construct($data, $notificationType)
    {
        $this->data = $data;
        $this->notificationType = $notificationType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {

        $notificationType = $this->notificationType;
        $msgBody = $notificationType->default_text;
        $msgBody = str_replace('[[user_name]]', $this->data['user_name'], $msgBody);
        $route = route('customers.details', $this->data['user_id']);
        return (new WebPushMessage)
            ->title($notificationType->name)
            ->icon(static_asset('assets/img/app_logo.png'))
            ->body($msgBody)
            ->action('View', 'view_notification')
            ->data(['url' => $route])
            ->options(['TTL' => 1000]);
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'data' => [
               'user_id'    => $this->data['user_id'],
               'user_name'  => $this->data['user_name'],
            ]
        ];
    }
}
