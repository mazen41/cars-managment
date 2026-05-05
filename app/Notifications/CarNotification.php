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

class CarNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $type;
    public $data;
    public $className;

    // Notification types
    const TYPE_CAR_ADDED = 'car_added';
    const TYPE_CAR_UPDATED = 'car_updated';
    const TYPE_CAR_STATUS_CHANGED = 'car_status_changed';
    const TYPE_CAR_MODERATION_STATUS_CHANGED = 'car_moderation_status_changed';
    const TYPE_CAR_APPROVED = 'car_approved';
    const TYPE_CAR_REJECTED = 'car_rejected';
    const TYPE_CAR_PUBLISHED = 'car_published';
    const TYPE_CAR_UNPUBLISHED = 'car_unpublished';
    const TYPE_CAR_SOLD = 'car_sold';
    const TYPE_CAR_RESERVED = 'car_reserved';
    const TYPE_CAR_FEATURED = 'car_featured';
    const TYPE_CAR_DELETED = 'car_deleted';

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
        $this->className = CarNotification::class;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'database',
            WebPushChannel::class,
            FcmChannel::class
        ];
    }

    /**
     * Determine if SMS should be sent for this notification type.
     *
     * @return bool
     */
    protected function shouldSendSms(): bool
    {
        // Send SMS for critical notifications
        return in_array($this->type, [
            self::TYPE_CAR_APPROVED,
            self::TYPE_CAR_REJECTED,
            self::TYPE_CAR_SOLD,
            self::TYPE_CAR_DELETED,
        ]);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = new MailMessage();

        switch ($this->type) {
            case self::TYPE_CAR_ADDED:
                return $mailMessage
                    ->subject(translate('New Car Added'))
                    ->line(translate('A new car has been added to the platform.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Seller') . ': ' . $this->data['seller_name'])
                    ->line(translate('Price') . ': ' . $this->formatCurrency($this->data['price']))
                    ->action(translate('View Car'), $this->data['url'])
                    ->line(translate('Please review and moderate this listing.'));

            case self::TYPE_CAR_UPDATED:
                return $mailMessage
                    ->subject(translate('Car Updated'))
                    ->line(translate('A car listing has been updated.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->when(!empty($this->data['changes']), function ($message) {
                        return $message->line(translate('Changes') . ': ' . $this->data['changes']);
                    })
                    ->action(translate('View Car'), $this->data['url'])
                    ->line(translate('Review the updated information.'));

            case self::TYPE_CAR_STATUS_CHANGED:
                return $mailMessage
                    ->subject(translate('Car Status Updated'))
                    ->line(translate('Your car status has been updated.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Previous status') . ': ' . ucfirst($this->data['old_status']))
                    ->line(translate('New status') . ': ' . ucfirst($this->data['new_status']))
                    ->when(!empty($this->data['reason']), function ($message) {
                        return $message->line(translate('Reason') . ': ' . $this->data['reason']);
                    })
                    ->action(translate('View Car'), $this->data['url'])
                    ->line(translate('Thank you for using our platform.'));

            case self::TYPE_CAR_MODERATION_STATUS_CHANGED:
                return $mailMessage
                    ->subject(translate('Car Moderation Status Updated'))
                    ->line(translate('Your car moderation status has been updated.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Previous status') . ': ' . ucfirst($this->data['old_moderation_status']))
                    ->line(translate('New status') . ': ' . ucfirst($this->data['new_moderation_status']))
                    ->when(!empty($this->data['notes']), function ($message) {
                        return $message->line(translate('Notes') . ': ' . $this->data['notes']);
                    })
                    ->action(translate('View Car'), $this->data['url'])
                    ->line(translate('Thank you for using our platform.'));

            case self::TYPE_CAR_APPROVED:
                return $mailMessage
                    ->subject(translate('Car Approved'))
                    ->line(translate('Congratulations! Your car has been approved.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->when(!empty($this->data['notes']), function ($message) {
                        return $message->line(translate('Notes') . ': ' . $this->data['notes']);
                    })
                    ->action(translate('View Car'), $this->data['url'])
                    ->line(translate('Your car is now visible to buyers.'));

            case self::TYPE_CAR_REJECTED:
                return $mailMessage
                    ->subject(translate('Car Rejected'))
                    ->line(translate('Your car listing has been rejected.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Reason') . ': ' . ($this->data['reason'] ?? translate('Not specified')))
                    ->when(!empty($this->data['notes']), function ($message) {
                        return $message->line(translate('Additional notes') . ': ' . $this->data['notes']);
                    })
                    ->action(translate('View Car'), $this->data['url'])
                    ->line(translate('Please make the necessary changes and resubmit.'));

            case self::TYPE_CAR_PUBLISHED:
                return $mailMessage
                    ->subject(translate('Car Published'))
                    ->line(translate('Your car has been published successfully.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Price') . ': ' . $this->formatCurrency($this->data['price']))
                    ->action(translate('View Car'), $this->data['url'])
                    ->line(translate('Your car is now visible to potential buyers.'));

            case self::TYPE_CAR_UNPUBLISHED:
                return $mailMessage
                    ->subject(translate('Car Unpublished'))
                    ->line(translate('Your car has been unpublished.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->when(!empty($this->data['reason']), function ($message) {
                        return $message->line(translate('Reason') . ': ' . $this->data['reason']);
                    })
                    ->action(translate('View Car'), $this->data['url'])
                    ->line(translate('Your car is no longer visible to buyers.'));

            case self::TYPE_CAR_SOLD:
                return $mailMessage
                    ->subject(translate('Car Sold'))
                    ->line(translate('Congratulations! Your car has been sold.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Sale price') . ': ' . $this->formatCurrency($this->data['price']))
                    ->when(!empty($this->data['buyer_name']), function ($message) {
                        return $message->line(translate('Buyer') . ': ' . $this->data['buyer_name']);
                    })
                    ->action(translate('View Details'), $this->data['url'])
                    ->line(translate('Thank you for using our platform!'));

            case self::TYPE_CAR_RESERVED:
                return $mailMessage
                    ->subject(translate('Car Reserved'))
                    ->line(translate('Your car has been reserved.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->when(!empty($this->data['reserved_by']), function ($message) {
                        return $message->line(translate('Reserved by') . ': ' . $this->data['reserved_by']);
                    })
                    ->when(!empty($this->data['reserved_until']), function ($message) {
                        return $message->line(translate('Reserved until') . ': ' . $this->data['reserved_until']);
                    })
                    ->action(translate('View Car'), $this->data['url'])
                    ->line(translate('The car is temporarily unavailable for other buyers.'));

            case self::TYPE_CAR_FEATURED:
                return $mailMessage
                    ->subject(translate('Car Featured'))
                    ->line(translate('Great news! Your car has been featured.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->action(translate('View Car'), $this->data['url'])
                    ->line(translate('Featured cars get more visibility and attract more buyers.'));

            case self::TYPE_CAR_DELETED:
                return $mailMessage
                    ->subject(translate('Car Deleted'))
                    ->line(translate('A car listing has been deleted.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->when(!empty($this->data['reason']), function ($message) {
                        return $message->line(translate('Reason') . ': ' . $this->data['reason']);
                    })
                    ->when(!empty($this->data['deleted_by']), function ($message) {
                        return $message->line(translate('Deleted by') . ': ' . $this->data['deleted_by']);
                    })
                    ->line(translate('If you have any questions, please contact support.'));

            default:
                return $mailMessage
                    ->subject(translate('Car Notification'))
                    ->line(translate('You have a new car notification.'))
                    ->action(translate('View Details'), $this->data['url'] ?? url('/'));
        }
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
     * Get the web push representation of the notification.
     *
     * @param  mixed  $notifiable
     * @param  mixed  $notification
     * @return \NotificationChannels\WebPush\WebPushMessage
     */
    public function toWebPush($notifiable, $notification)
    {
        $title = $this->getTitle();
        $body = $this->getBody();
        $url = $this->getUrl();

        return (new WebPushMessage)
            ->title($title)
            ->icon(static_asset('assets/img/app_logo.png'))
            ->body($body)
            ->data(['url' => $url])
            ->options(['TTL' => 1000]);
    }

    /**
     * Get the FCM notification data.
     *
     * @return FcmMessage
     */
    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
                title: $this->getTitle(),
                body: $this->getBody()
            )))
            ->data($this->data)
            ->custom([
                'item_type' => 'car',
                'item_type_id' => $this->data['car_id'] ?? null,
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
     * Get the FCM data payload.
     *
     * @return array
     */
    public function toData()
    {
        return [
            'type' => $this->type,
            'url' => $this->getUrl(),
            'data' => $this->data,
        ];
    }

    /**
     * Get the notification title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        switch ($this->type) {
            case self::TYPE_CAR_ADDED:
                return translate('New Car Added');
            case self::TYPE_CAR_UPDATED:
                return translate('Car Updated');
            case self::TYPE_CAR_STATUS_CHANGED:
                return translate('Car Status Changed');
            case self::TYPE_CAR_MODERATION_STATUS_CHANGED:
                return translate('Moderation Status Changed');
            case self::TYPE_CAR_APPROVED:
                return translate('Car Approved');
            case self::TYPE_CAR_REJECTED:
                return translate('Car Rejected');
            case self::TYPE_CAR_PUBLISHED:
                return translate('Car Published');
            case self::TYPE_CAR_UNPUBLISHED:
                return translate('Car Unpublished');
            case self::TYPE_CAR_SOLD:
                return translate('Car Sold');
            case self::TYPE_CAR_RESERVED:
                return translate('Car Reserved');
            case self::TYPE_CAR_FEATURED:
                return translate('Car Featured');
            case self::TYPE_CAR_DELETED:
                return translate('Car Deleted');
            default:
                return translate('Car Notification');
        }
    }

    /**
     * Get the notification body.
     *
     * @return string
     */
    public function getBody(): string
    {
        switch ($this->type) {
            case self::TYPE_CAR_ADDED:
                return translate('New car added by') . ' ' . $this->data['seller_name'] . ': ' . $this->data['car_name'];
            case self::TYPE_CAR_UPDATED:
                return $this->data['car_name'] . ' ' . translate('has been updated');
            case self::TYPE_CAR_STATUS_CHANGED:
                return $this->data['car_name'] . ' ' . translate('status changed to') . ' ' . $this->data['new_status'];
            case self::TYPE_CAR_MODERATION_STATUS_CHANGED:
                return $this->data['car_name'] . ' ' . translate('moderation status changed to') . ' ' . $this->data['new_moderation_status'];
            case self::TYPE_CAR_APPROVED:
                return translate('Your car') . ' ' . $this->data['car_name'] . ' ' . translate('has been approved!');
            case self::TYPE_CAR_REJECTED:
                return translate('Your car') . ' ' . $this->data['car_name'] . ' ' . translate('has been rejected');
            case self::TYPE_CAR_PUBLISHED:
                return $this->data['car_name'] . ' ' . translate('is now published and visible to buyers');
            case self::TYPE_CAR_UNPUBLISHED:
                return $this->data['car_name'] . ' ' . translate('has been unpublished');
            case self::TYPE_CAR_SOLD:
                return translate('Congratulations!') . ' ' . $this->data['car_name'] . ' ' . translate('has been sold for') . ' ' . $this->formatCurrency($this->data['price']);
            case self::TYPE_CAR_RESERVED:
                return $this->data['car_name'] . ' ' . translate('has been reserved');
            case self::TYPE_CAR_FEATURED:
                return $this->data['car_name'] . ' ' . translate('is now featured!');
            case self::TYPE_CAR_DELETED:
                return $this->data['car_name'] . ' ' . translate('has been deleted');
            default:
                return translate('You have a new car notification');
        }
    }

    /**
     * Get the notification URL.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->data['url'] ?? url('/');
    }

    /**
     * returns the id of the reservation
     */
    public function getTypeId() {
        return $this->data['car_id'];
    }

    /**
     * Get SMS message for this notification.
     *
     * @return string
     */
    public function getSmsMessage(): string
    {
        switch ($this->type) {
            case self::TYPE_CAR_APPROVED:
                return translate('Congratulations! Your car') . ' ' . $this->data['car_name'] . ' ' . translate('has been approved and is now visible to buyers.');
            case self::TYPE_CAR_REJECTED:
                return translate('Your car') . ' ' . $this->data['car_name'] . ' ' . translate('has been rejected.') . ' ' . translate('Reason') . ': ' . ($this->data['reason'] ?? translate('Not specified'));
            case self::TYPE_CAR_SOLD:
                return translate('Congratulations! Your car') . ' ' . $this->data['car_name'] . ' ' . translate('has been sold for') . ' ' . $this->formatCurrency($this->data['price']) . '.';
            case self::TYPE_CAR_DELETED:
                return translate('Your car') . ' ' . $this->data['car_name'] . ' ' . translate('has been deleted.') . ' ' . translate('Contact support if you have questions.');
            default:
                return translate('You have a new car notification. Please check your account.');
        }
    }

    /**
     * Format currency value.
     *
     * @param float $amount
     * @return string
     */
    protected function formatCurrency(float $amount): string
    {
        return number_format($amount, 2) . ' ' . ($this->data['currency'] ?? get_setting('system_default_currency'));
    }
}
