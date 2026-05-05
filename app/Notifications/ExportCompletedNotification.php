<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use App\Models\NotificationType;

class ExportCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $data;
    public $className;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($export_notification)
    {
        $this->data = $export_notification;
        $this->className = self::class;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', DbNotification::class, WebPushChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $notificationType = NotificationType::find($this->data['notification_type_id']);
        $downloadUrl = route('exports.download', ['file' => $this->data['file_name']]);

        $msgBody = $notificationType->default_text;
        $msgBody = str_replace('[[file_name]]', $this->data['file_name'], $msgBody);

        return (new MailMessage)
            ->subject($notificationType->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($msgBody)
            ->action('Download Export', $downloadUrl)
            ->line('The export file will be available for download for the next 24 hours.')
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
                'file_name' => $this->data['file_name'],
                'file_path' => $this->data['file_path'],
                'user_id'   => $this->data['user_id'],
            ]
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        $route = route('exports.download', ['file' => $this->data['file_name']]);

        $notificationType = NotificationType::find($this->data['notification_type_id']);

        $msgBody = $notificationType->default_text;
        $msgBody = str_replace('[[file_name]]', $this->data['file_name'], $msgBody);

        return (new WebPushMessage)
            ->title($notificationType->name)
            ->icon(static_asset('assets/img/app_logo.png'))
            ->body($msgBody)
            ->data(['url' => $route])
            ->options(['TTL' => 1000]);
    }
}
