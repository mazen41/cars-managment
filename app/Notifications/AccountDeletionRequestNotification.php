<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use App\Models\NotificationType;
use App\Models\User;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class AccountDeletionRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;


    public $title;
    public $body;
    public $data;
    public $user;
    public $notificationType;
    public $className = AccountDeletionRequestNotification::class;
    /**
     * Create a new notification instance.
     */
    public function __construct($notificationData)
    {
        $this->user = User::find($notificationData['user_id']);
        $this->title = translate('Account Deletion Request');
        $this->body = translate('Your account is scheduled for deletion in 30 days.');
        $this->notificationType = NotificationType::find($notificationData['notification_type_id']);
        $this->data = [
            'username' => $this->user->name,
            'user_email' => $this->user->email,
            'notification_type_id' => $notificationData['notification_type_id'],
            'action_url' => route('cancel_account_delete'),
            'user_id' => $this->user->id,
        ];
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            //'mail',
            WebPushChannel::class,
           // DbNotification::class,
           FcmChannel::class
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return $notifiable->user_type == 'customer' ? (new MailMessage)
                    ->subject('Account Deletion Request Received')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('We have received a request to delete your account.')
                    ->line('If you did not make this request, please secure your account immediately by changing your password.')
                    ->line('Your account will be scheduled for deletion within the next 30 days. During this period, you can log in to cancel the deletion request.')
                    ->action('Cancel Deletion Request', route('cancel_account_delete'))
                    ->line('After 30 days, all your data will be permanently removed from our systems.')
                    ->line('We\'re sorry to see you go. If you have any feedback that might help us improve our service, please let us know.')
                    ->salutation('Thank you for being a part of our community.')

                    : (new MailMessage)
                    ->subject('Account Deletion Request Received')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('A user has requested to delete their account.')
                    ->line('User Details:')
                    ->line('Name: ' . $this->user->name)
                    ->line($this->user->email? 'Email: ' . $this->user->email : '' )
                    ->line($this->user->phone? 'Phone: ' . $this->user->phone : '' )
                    ->line('Please review the request and take necessary actions.')
                    ->action('Review Request', route('customers.details', $this->user->id));


    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush(object $notifiable, $notification)
    {
        $msgBody = $this->notificationType->default_text;
        $msgBody = str_replace('[[username]]', $this->user->name, $msgBody);
        return  $notifiable->user_type == 'customer' ?  (new WebPushMessage)
            ->title($this->notificationType->name)
            ->icon(static_asset('assets/img/app_logo.png'))
            ->body($msgBody)
            ->action('Review Request', 'settings/cancel-deletion')
            ->data(['url' => route('cancel_account_delete')])

            : (new WebPushMessage)
            ->title($this->notificationType->name)
            ->icon(static_asset('assets/img/app_logo.png'))
            ->body($msgBody)
            ->action('Review Request', route('customers.details', $this->user->id))
            ->data(['url' => route('customers.details', $this->user->id)]);
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

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'notification_type_id' => $this->data['notification_type_id'],
            'data' => $this->data,
        ];
    }
}
