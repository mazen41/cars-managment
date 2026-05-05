<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use App\Models\NotificationType;

class SupportTicketNotification extends Notification implements ShouldQueue
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
        $this->className= SupportTicketNotification::class;
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
                'name'  => $this->data['user_name'],
                'user_id'    => $this->data['user_id'],
                'ticket_id'=> $this->data['ticket_id'],
                'ticket_code' => $this->data['ticket_code']
            ]
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        $notificationType = NotificationType::where('id',$this->data['notification_type_id'])->first();
        $route = route('support_ticket.admin_show', encrypt($notification->data['ticket_id']));
        $msgBody = $notificationType->default_text;
        $msgBody = str_replace('[[ticket_code]]', $this->data['ticket_code'], $msgBody);
        return (new WebPushMessage)
            ->title($notificationType->name)
            ->icon(static_asset('assets/img/app_logo.png'))
            ->body($msgBody)
            ->data(['url' =>$route])
            ->options(['TTL' => 1000]);
    }
}
