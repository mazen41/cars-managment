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

class AdminCarInspectionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $type;
    public $data;
    public $className;

    // Admin notification types for car inspections
    const TYPE_INSPECTION_PAYMENT_RECEIVED = 'inspection_payment_received_admin';
    const TYPE_HIGH_VALUE_INSPECTION = 'high_value_inspection_admin';
    const TYPE_INSPECTION_COMPLETED = 'inspection_completed_admin';
    const TYPE_INSPECTION_FAILED = 'inspection_failed_admin';
    const TYPE_INSPECTION_DISPUTE = 'inspection_dispute_admin';
    const TYPE_INSPECTION_REFUND_REQUEST = 'inspection_refund_request_admin';

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
        $this->className = AdminCarInspectionNotification::class;
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
        $url = $this->data['admin_url'] ?? '/admin/car-inspections';

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
        $url = $this->data['admin_url'] ?? url('/admin/car-inspections');

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
    private function getTitle(): string
    {
        switch ($this->type) {
            case self::TYPE_INSPECTION_PAYMENT_RECEIVED:
                return translate('Car Inspection Payment Received');
            case self::TYPE_HIGH_VALUE_INSPECTION:
                return translate('High Value Car Inspection');
            case self::TYPE_INSPECTION_COMPLETED:
                return translate('Car Inspection Completed');
            case self::TYPE_INSPECTION_FAILED:
                return translate('Car Inspection Failed');
            case self::TYPE_INSPECTION_DISPUTE:
                return translate('Car Inspection Dispute');
            case self::TYPE_INSPECTION_REFUND_REQUEST:
                return translate('Car Inspection Refund Request');
            default:
                return translate('Car Inspection Admin Alert');
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
        $paymentMethod = $this->data['payment_method'] ?? translate('Unknown');
        $totalScore = $this->data['total_score'] ?? null;
        $overallCondition = $this->data['overall_condition'] ?? null;

        switch ($this->type) {
            case self::TYPE_INSPECTION_PAYMENT_RECEIVED:
                return translate('Payment of') . " {$currency} {$amount} " . translate('received via') . " {$paymentMethod} " . translate('for') . " {$carName} " . translate('inspection by') . " {$customerName}.";
            case self::TYPE_HIGH_VALUE_INSPECTION:
                return translate('High value inspection of') . " {$currency} {$amount} " . translate('booked for') . " {$carName} " . translate('by') . " {$customerName}.";
            case self::TYPE_INSPECTION_COMPLETED:
                $scoreText = $totalScore ? ' ' . translate('with score') . " {$totalScore}" : '';
                $conditionText = $overallCondition ? ' (' . translate('Condition') . ": {$overallCondition})" : '';
                return translate('Inspection for') . " {$carName} " . translate('completed by') . " {$inspectorName}{$scoreText}{$conditionText}.";
            case self::TYPE_INSPECTION_FAILED:
                return translate('Inspection for') . " {$carName} " . translate('by') . " {$inspectorName} " . translate('has failed and requires attention') . ".";
            case self::TYPE_INSPECTION_DISPUTE:
                return translate('Dispute raised for') . " {$carName} " . translate('inspection by') . " {$customerName}.";
            case self::TYPE_INSPECTION_REFUND_REQUEST:
                return translate('Refund request submitted for') . " {$carName} " . translate('inspection by') . " {$customerName}.";
            default:
                return translate('Admin attention required for') . " {$carName} " . translate('inspection by') . " {$customerName}.";
        }
    }

    /**
     * Get Admin panel route
     */
    public function getUrl(){
         return $this->data['admin_url'] ?? '/admin/car-inspections';
    }
}
