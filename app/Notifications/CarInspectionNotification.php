<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class CarInspectionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $type;
    public $data;
    public $className;

    // Notification types
    const TYPE_INSPECTION_PAID = 'inspection_paid';
    const TYPE_INSPECTION_SCHEDULED = 'inspection_scheduled';
    const TYPE_INSPECTION_STARTED = 'inspection_started';
    const TYPE_INSPECTION_COMPLETED = 'inspection_completed';
    const TYPE_INSPECTION_CANCELLED = 'inspection_cancelled';
    const TYPE_INSPECTION_FAILED = 'inspection_failed';

    /**
     * Create a new notification instance.
     *
     * @param string $type
     * @param array $data
     * @return void
     */
    public function __construct(string $type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
        $this->className = CarInspectionNotification::class;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = ['database'];


        // Sellers and customers get FCM notifications
        if (in_array($notifiable->user_type, ['seller', 'customer']) && $notifiable->device_token) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
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
            'type' => $this->type,
            'data' => $this->data,
            'className' => $this->className,
        ];
    }

    /**
     * Get the WebPush representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return WebPushMessage
     */
    public function toWebPush($notifiable)
    {
        $title = $this->getTitle();
        $body = $this->getBody();
        $url = $this->data['url'] ?? '/';

        return (new WebPushMessage)
            ->title($title)
            ->body($body)
            ->action('View Details', 'view_details')
            ->data(['url' => $url])
            ->badge('/images/notification-badge.png')
            ->icon('/images/notification-icon.png');
    }

    /**
     * Get the FCM representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return FcmMessage
     */
    public function toFcm($notifiable)
    {
        $title = $this->getTitle();
        $body = $this->getBody();
        $url = $this->data['url'] ?? '/';

        return (new FcmMessage(notification: new FcmNotification(
            title: $title,
            body: $body,
        )))
        ->data([
            'type' => $this->type,
            'url' => $url,
            'data' => json_encode($this->data)
        ])
        ->custom([
            'item_type' => 'car_inspection',
            'item_type_id' => $this->data['inspection_id'] ?? null,
            'android' => [
                'notification' => [
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ],
            ],
        ]);
    }

    /**
     * returns the id of the reservation
     */
    public function getTypeId() {
        return $this->data['inspection_id'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $title = $this->getTitle();
        $body = $this->getBody();
        $url = $this->data['url'] ?? url('/');

        return (new MailMessage)
            ->subject($title)
            ->line($body)
            ->action('View Details', $url);
    }

    /**
     * Get the notification title based on type
     *
     * @return string
     */
    public function getTitle(): string
    {
        switch ($this->type) {
            case self::TYPE_INSPECTION_PAID:
                return translate('Car Inspection Payment Received');
            case self::TYPE_INSPECTION_SCHEDULED:
                return translate('Car Inspection Scheduled');
            case self::TYPE_INSPECTION_STARTED:
                return translate('Car Inspection Started');
            case self::TYPE_INSPECTION_COMPLETED:
                return translate('Car Inspection Completed');
            case self::TYPE_INSPECTION_CANCELLED:
                return translate('Car Inspection Cancelled');
            case self::TYPE_INSPECTION_FAILED:
                return translate('Car Inspection Failed');
            default:
                return translate('Car Inspection Update');
        }
    }

    /**
     * Get the notification body based on type
     *
     * @return string
     */
    public function getBody(): string
    {
        $carName = $this->data['car_name'] ?? translate('Car');
        $customerName = $this->data['customer_name'] ?? translate('Customer');
        $inspectorName = $this->data['inspector_name'] ?? translate('Inspector');
        $amount = $this->data['amount'] ?? 0;
        $currency = $this->data['currency'] ?? currency_symbol();
        $scheduledAt = $this->data['scheduled_at'] ?? null;
        $totalScore = $this->data['total_score'] ?? null;
        $overallCondition = $this->data['overall_condition'] ?? null;

        switch ($this->type) {
            case self::TYPE_INSPECTION_PAID:
                return translate('Payment of') . " {$currency} {$amount} " . translate('received for') . " {$carName} " . translate('inspection by') . " {$customerName}.";
            case self::TYPE_INSPECTION_SCHEDULED:
                $dateTime = $scheduledAt ? date('M j, Y \a\t g:i A', strtotime($scheduledAt)) : translate('soon');
                return translate('Car inspection for') . " {$carName} " . translate('has been scheduled for') . " {$dateTime} " . translate('with') . " {$inspectorName}.";
            case self::TYPE_INSPECTION_STARTED:
                return translate('Car inspection for') . " {$carName} " . translate('has been started by') . " {$inspectorName}.";
            case self::TYPE_INSPECTION_COMPLETED:
                $scoreText = $totalScore ? ' ' . translate('with a score of') . " {$totalScore}" : '';
                $conditionText = $overallCondition ? ' (' . translate('Condition') . ": {$overallCondition})" : '';
                return translate('Car inspection for') . " {$carName} " . translate('has been completed') . "{$scoreText}{$conditionText}.";
            case self::TYPE_INSPECTION_CANCELLED:
                return translate('Car inspection for') . " {$carName} " . translate('has been cancelled') . ".";
            case self::TYPE_INSPECTION_FAILED:
                return translate('Car inspection for') . " {$carName} " . translate('could not be completed due to technical issues') . ".";
            default:
                return translate('Update regarding car inspection for') . " {$carName}.";
        }
    }

    /**
     * Get SMS message content
     *
     * @return string
     */
    public function getSmsMessage(): string
    {
        return $this->getTitle() . ': ' . $this->getBody();
    }
    /**
     * Get Admin panel route
     */
    public function getUrl(){
         return $this->data['admin_url'] ?? '/admin/car-inspections';
    }
}
