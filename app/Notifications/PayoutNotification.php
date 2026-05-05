<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use App\Models\NotificationType;

class PayoutNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $data;
    public $className;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->className= PayoutNotification::class;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [DbNotification::class, WebPushChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'notification_type_id' => $this->data['notification_type_id'],
            'data' => [
                'user_id'        => $this->data['user']['id'],
                'user_type'      => $this->data['user']['user_type'],
                'name'           => $this->data['user']['name'],
                'payment_amount' => $this->data['amount'],
                'status'         => $this->data['status']
            ]
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        $notificationType = NotificationType::where('id',$this->data['notification_type_id'])->first();

        $route = route('withdraw_requests_all');
        $msgBody = $notificationType->default_text;
        $msgBody = str_replace('[[shop_name]]', $this->data['user']['name'], $msgBody);
        $msgBody = str_replace('[[amount]]', $this->data['amount'], $msgBody);
        return (new WebPushMessage)
            ->title($notificationType->name)
            ->icon(static_asset('assets/img/app_logo.png'))
            ->body($msgBody)
            ->data(['url'=>$route])
            ->options(['TTL' => 1000]);
    }
}
