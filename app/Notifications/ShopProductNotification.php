<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use App\Models\NotificationType;

class ShopProductNotification extends Notification implements ShouldQueue
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
        $this->data  = $data;
        $this->className= ShopProductNotification::class;
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
                'id'        => $this->data['product']['id'],
                'name'      => $this->data['product']['name'],
                'status'    => $this->data['status'],
                'type'      => $this->data['product_type']
            ]
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        $notificationType = NotificationType::where('id',$this->data['notification_type_id'])->first();
        $route = route('products.seller', $this->data['product_type']);
        $msgBody =$notificationType->default_text;
        $msgBody = str_replace('[[product_name]]', $this->data['product']['name'], $msgBody);
        return (new WebPushMessage)
            ->title($notificationType->name)
            ->icon(static_asset('assets/img/app_logo.png'))
            ->body($msgBody)
            ->data(['url'=> $route])
            ->options(['TTL' => 1000]);
    }
}
