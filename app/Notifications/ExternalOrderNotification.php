<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use App\Models\NotificationType;

class ExternalOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $data;
    public $className;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($order_notification)
    {
        $this->data = $order_notification;
        $this->className= ExternalOrderNotification::class;
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
                'order_id'      => $this->data['order_id'],
                'order_code'    => $this->data['order_code'],
                'user_id'       => $this->data['user_id'],
                'status'        => $this->data['status'],
                'payment_method'        => $this->data['payment_method'],
                'amount'        => $this->data['amount'],
            ]
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        if($notifiable->user_type == 'admin'){
            $route = route('external_orders.show', encrypt($this->data['order_id']));
        } else {
            $route = route('home');
        }
        $notificationType = NotificationType::where('id',$this->data['notification_type_id'])->first();
        $msgBody = $notificationType->default_text;
        $msgBody = str_replace('[[order_code]]', $this->data['order_code'], $msgBody);
        $msgBody = str_replace('[[status]]', $this->data['status'], $msgBody);
        $msgBody = str_replace('[[payment_method]]', translate($this->data['payment_method']), $msgBody);
        $msgBody = str_replace('[[amount]]', convert_price_from_usd($this->data['amount'], true), $msgBody);
        return (new WebPushMessage)
            ->title($notificationType->name)
            ->icon(static_asset('assets/img/app_logo.png'))
            ->body($msgBody)
            ->data(['url'=> $route])
            ->options(['TTL' => 1000]);
    }
}
