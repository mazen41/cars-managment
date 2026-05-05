<?php

namespace App\Notifications;

use App\Models\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $data;
    public $className;

    public  $title;
    public $body;
    /**
     * Create a new notification instance.
     */
   public function __construct($notification_data)
    {
        $this->data = $notification_data;
        $this->className= NewMessageNotification::class;
        $this->title = $notification_data['title'] ?? translate('New Message');
        $this->body = $notification_data['body'] ?? translate('You have a new message');
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            WebPushChannel::class,
            //DbNotification::class,
           //'fcm'
        ];
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
           'notification_type_id' => $this->data['notification_type_id'],
           'data'=>$this->data,
        ];
    }

     public function toWebPush($notifiable, $notification)
    {
        $route = '';
        switch($notifiable->user_type){
            case "admin":
            case "staff":
                $route = route('conversations.admin_show', encrypt(value: $this->data['conversation_id']));
                break;
            case "seller":
                $route = route('seller.conversations.show', encrypt(value: $this->data['conversation_id']));
                break;
            case "customer":
                $route = route('conversations.show', encrypt(value: $this->data['conversation_id']));
                break;
            default:

        }
        $notificationType = NotificationType::where('id',$this->data['notification_type_id'])->first();
        $msgBody = $notificationType->default_text;

        $msgBody = str_replace('[[message]]', $this->data['message'], $msgBody);
        return (new WebPushMessage)
            ->title($notificationType->name)
            ->icon(static_asset('assets/img/app_logo.png'))
            ->body($msgBody)
            ->data(['url'=> $route])
            ->options(['TTL' => 1000]);
    }

     public function toData() {
        return $this->data;
    }

     public function toFCM()
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
        ];
    }
}
