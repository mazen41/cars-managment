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

class CarReservationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $type;
    public $data;
    public $className;

    // Notification types
    const TYPE_RESERVATION_PAID = 'reservation_paid';
    const TYPE_RESERVATION_CONFIRMED = 'reservation_confirmed';
    const TYPE_RESERVATION_CANCELLED = 'reservation_cancelled';
    const TYPE_RESERVATION_EXPIRED = 'reservation_expired';
    const TYPE_RESERVATION_COMPLETED = 'reservation_completed';

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
        $this->className = CarReservationNotification::class;
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
     * returns the id of the reservation
     */
    public function getTypeId() {
        return $this->data['reservation_id'];
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
            'item_type' => 'car_reservation',
            'item_type_id' => $this->data['reservation_id'] ?? null,
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
            case self::TYPE_RESERVATION_PAID:
                return translate('Car Reservation Payment Received');
            case self::TYPE_RESERVATION_CONFIRMED:
                return translate('Car Reservation Confirmed');
            case self::TYPE_RESERVATION_CANCELLED:
                return translate('Car Reservation Cancelled');
            case self::TYPE_RESERVATION_EXPIRED:
                return translate('Car Reservation Expired');
            case self::TYPE_RESERVATION_COMPLETED:
                return translate('Car Reservation Completed');
            default:
                return translate('Car Reservation Update');
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
        $amount = $this->data['amount'] ?? 0;
        $currency = $this->data['currency'] ?? currency_symbol();

        switch ($this->type) {
            case self::TYPE_RESERVATION_PAID:
                return translate('Payment of') . " {$currency} {$amount} " . translate('received for') . " {$carName} " . translate('reservation by') . " {$customerName}.";
            case self::TYPE_RESERVATION_CONFIRMED:
                return translate('Your reservation for') . " {$carName} " . translate('has been confirmed') . ".";
            case self::TYPE_RESERVATION_CANCELLED:
                return translate('Reservation for') . " {$carName} " . translate('has been cancelled') . ".";
            case self::TYPE_RESERVATION_EXPIRED:
                return translate('Your reservation for') . " {$carName} " . translate('has expired') . ".";
            case self::TYPE_RESERVATION_COMPLETED:
                return translate('Reservation for') . " {$carName} " . translate('has been completed successfully') . ".";
            default:
                return translate('Update regarding your car reservation for') . " {$carName}.";
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
         return $this->data['admin_url'] ?? '/admin/car-reservations';
    }
}
