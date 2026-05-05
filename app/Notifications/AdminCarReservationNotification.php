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

class AdminCarReservationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $type;
    public $data;
    public $className;

    // Admin notification types for car reservations
    const TYPE_RESERVATION_PAYMENT_RECEIVED = 'reservation_payment_received_admin';
    const TYPE_HIGH_VALUE_RESERVATION = 'high_value_reservation_admin';
    const TYPE_RESERVATION_CANCELLED = 'reservation_cancelled_admin';
    const TYPE_RESERVATION_DISPUTE = 'reservation_dispute_admin';
    const TYPE_RESERVATION_REFUND_REQUEST = 'reservation_refund_request_admin';

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
        $this->className = AdminCarReservationNotification::class;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', WebPushChannel::class];
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
        $url = $this->data['admin_url'] ?? '/admin/car-reservations';

        return (new WebPushMessage)
            ->title($title)
            ->body($body)
            ->action('View Details', 'view_details')
            ->data(['url' => $url])
            ->badge('/images/notification-badge.png')
            ->icon('/images/notification-icon.png');
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
        $url = $this->data['admin_url'] ?? url('/admin/car-reservations');

        return (new MailMessage)
            ->subject($title)
            ->line($body)
            ->action('View in Admin Panel', $url);
    }

    /**
     * Get the notification title based on type
     *
     * @return string
     */
    public function getTitle(): string
    {
        switch ($this->type) {
            case self::TYPE_RESERVATION_PAYMENT_RECEIVED:
                return translate('Car Reservation Payment Received');
            case self::TYPE_HIGH_VALUE_RESERVATION:
                return translate('High Value Car Reservation');
            case self::TYPE_RESERVATION_CANCELLED:
                return translate('Car Reservation Cancelled');
            case self::TYPE_RESERVATION_DISPUTE:
                return translate('Car Reservation Dispute');
            case self::TYPE_RESERVATION_REFUND_REQUEST:
                return translate('Car Reservation Refund Request');
            default:
                return translate('Car Reservation Admin Alert');
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
        $paymentMethod = $this->data['payment_method'] ?? translate('Unknown');

        switch ($this->type) {
            case self::TYPE_RESERVATION_PAYMENT_RECEIVED:
                return translate('Payment of') . " {$currency} {$amount} " . translate('received via') . " {$paymentMethod} " . translate('for') . " {$carName} " . translate('reservation by') . " {$customerName}.";
            case self::TYPE_HIGH_VALUE_RESERVATION:
                return translate('High value reservation of') . " {$currency} {$amount} " . translate('made for') . " {$carName} " . translate('by') . " {$customerName}.";
            case self::TYPE_RESERVATION_CANCELLED:
                return translate('Reservation for') . " {$carName} " . translate('by') . " {$customerName} " . translate('has been cancelled') . ".";
            case self::TYPE_RESERVATION_DISPUTE:
                return translate('Dispute raised for') . " {$carName} " . translate('reservation by') . " {$customerName}.";
            case self::TYPE_RESERVATION_REFUND_REQUEST:
                return translate('Refund request submitted for') . " {$carName} " . translate('reservation by') . " {$customerName}.";
            default:
                return translate('Admin attention required for') . " {$carName} " . translate('reservation by') . " {$customerName}.";
        }
    }
    /**
     * Get Admin panel route
     */
    public function getUrl(){
         return $this->data['admin_url'] ?? '/admin/car-reservations';
    }
}
