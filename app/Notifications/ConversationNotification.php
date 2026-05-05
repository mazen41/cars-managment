<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use App\Models\NotificationType;

class ConversationNotification extends Notification implements ShouldQueue
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
        $this->className= ConversationNotification::class;
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
                'user_name'  => $this->data['user_name'],
                'user_id'    => $this->data['user_id'],
                'conversation_id'=> $this->data['conversation_id'],
            ]
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        $notificationType = NotificationType::where('id',$this->data['notification_type_id'])->first();
        $msgBody = $notificationType->default_text;
        $msgBody = str_replace('[[username]]', $this->data['user_name'], $msgBody);
        $user_type = $notifiable->user_type;
        $route = $user_type == 'admin' ? route('conversations.admin_show', encrypt($notification->data['conversation_id'])) :
            route('seller.conversations.show', encrypt($notification->data['conversation_id']));
        return (new WebPushMessage)
            ->title($notificationType->name)
            ->icon(static_asset('assets/img/app_logo.png'))
            ->body($msgBody)
            ->data(['url' =>$route])
            ->options(['TTL' => 1000]);
    }
}
