<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use App\Jobs\SendSmsToUser;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class AuctionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $type;
    public $data;
    public $className;

    // Notification types
    const TYPE_OUTBID = 'outbid';
    const TYPE_OFFER_RECEIVED = 'offer_received';
    const TYPE_OFFER_ACCEPTED = 'offer_accepted';
    const TYPE_OFFER_REJECTED = 'offer_rejected';
    const TYPE_INVOICE_GENERATED = 'invoice_generated';
    const TYPE_AUCTION_STARTING_SOON = 'auction_starting_soon';
    const TYPE_ITEM_SOLD = 'item_sold';
    const TYPE_INVOICE_STATUS_CHANGED = 'invoice_status_changed';
    const TYPE_PAYMENT_CONFIRMED = 'payment_confirmed';
    const TYPE_INVOICE_OVERDUE = 'invoice_overdue';
    const TYPE_INVOICE_CANCELLED = 'invoice_cancelled';
    const TYPE_PAYMENT_REMINDER = 'payment_reminder';
    const TYPE_INSURANCE_DEPOSIT_PAID = 'insurance_deposit_paid';
    const TYPE_INSURANCE_DEPOSIT_REFUND_REQUEST = 'insurance_deposit_refund_request';


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
        $this->className = AuctionNotification::class;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = [
            'database',
            WebPushChannel::class,
            FcmChannel::class
            ];

        // Add SMS channel if user has phone number
        if ($notifiable->phone && $this->shouldSendSms()) {
            // SMS will be sent via job dispatch in the listener
        }

        return $channels;
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
            self::TYPE_OFFER_ACCEPTED,
            self::TYPE_ITEM_SOLD,
            self::TYPE_PAYMENT_CONFIRMED,
            self::TYPE_INVOICE_OVERDUE,
            self::TYPE_INSURANCE_DEPOSIT_PAID,
            self::TYPE_INSURANCE_DEPOSIT_REFUND_REQUEST,
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
            case self::TYPE_OUTBID:
                return $mailMessage
                    ->subject(translate('You Have Been Outbid'))
                    ->line(translate('You have been outbid on') . ' ' . $this->data['car_name'])
                    ->line(translate('Current bid') . ': ' . $this->formatCurrency($this->data['current_price']))
                    ->line(translate('Your bid') . ': ' . $this->formatCurrency($this->data['your_bid']))
                    ->action(translate('View Auction'), $this->data['auction_url'])
                    ->line(translate('Place a higher bid to stay in the auction!'));

            case self::TYPE_OFFER_RECEIVED:
                return $mailMessage
                    ->subject(translate('New Offer Received'))
                    ->line(translate('You have received a new offer on') . ' ' . $this->data['car_name'])
                    ->line(translate('Offer amount') . ': ' . $this->formatCurrency($this->data['offer_amount']))
                    ->line(translate('Message') . ': ' . ($this->data['message'] ?? translate('No message')))
                    ->action(translate('View Offer'), $this->data['offer_url'])
                    ->line(translate('Review and respond to this offer.'));

            case self::TYPE_OFFER_ACCEPTED:
                return $mailMessage
                    ->subject(translate('Your Offer Has Been Accepted'))
                    ->line(translate('Congratulations! Your offer has been accepted.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Accepted amount') . ': ' . $this->formatCurrency($this->data['offer_amount']))
                    ->action(translate('View Invoice'), $this->data['invoice_url'])
                    ->line(translate('Please complete payment to finalize your purchase.'));

            case self::TYPE_OFFER_REJECTED:
                return $mailMessage
                    ->subject(translate('Your Offer Has Been Rejected'))
                    ->line(translate('Your offer on') . ' ' . $this->data['car_name'] . ' ' . translate('has been rejected.'))
                    ->line(translate('Offer amount') . ': ' . $this->formatCurrency($this->data['offer_amount']))
                    ->line(translate('Reason') . ': ' . ($this->data['reason'] ?? translate('Not specified')))
                    ->action(translate('View Auction'), $this->data['auction_url'])
                    ->line(translate('You can still participate in the live auction.'));

            case self::TYPE_INVOICE_GENERATED:
                return $mailMessage
                    ->subject(translate('Auction Invoice Generated'))
                    ->line(translate('An invoice has been generated for your auction purchase.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Amount') . ': ' . $this->formatCurrency($this->data['amount']))
                    ->action(translate('View Invoice'), $this->data['invoice_url'])
                    ->line(translate('Please complete payment within the specified timeframe.'));

            case self::TYPE_AUCTION_STARTING_SOON:
                return $mailMessage
                    ->subject(translate('Auction Starting Soon'))
                    ->line(translate('The auction for') . ' ' . $this->data['car_name'] . ' ' . translate('is starting soon!'))
                    ->line(translate('Start time') . ': ' . $this->data['start_time'])
                    ->action(translate('Join Auction'), $this->data['auction_url'])
                    ->line(translate('Make sure you have paid your insurance deposit.'));

            case self::TYPE_ITEM_SOLD:
                if ($this->data['is_winner']) {
                    return $mailMessage
                        ->subject(translate('Congratulations! You Won the Auction'))
                        ->line(translate('Congratulations! You have won the auction.'))
                        ->line(translate('Car') . ': ' . $this->data['car_name'])
                        ->line(translate('Winning bid') . ': ' . $this->formatCurrency($this->data['final_price']))
                        ->action(translate('View Invoice'), $this->data['invoice_url'])
                        ->line(translate('Please complete payment to finalize your purchase.'));
                } else {
                    return $mailMessage
                        ->subject(translate('Your Item Has Been Sold'))
                        ->line(translate('Your car has been sold at auction!'))
                        ->line(translate('Car') . ': ' . $this->data['car_name'])
                        ->line(translate('Final price') . ': ' . $this->formatCurrency($this->data['final_price']))
                        ->action(translate('View Payout'), $this->data['invoice_url'])
                        ->line(translate('Your payout will be processed after buyer payment.'));
                }

            case self::TYPE_INVOICE_STATUS_CHANGED:
                return $mailMessage
                    ->subject(translate('Invoice Status Updated'))
                    ->line(translate('Your invoice status has been updated.'))
                    ->line(translate('Invoice ID') . ': ' . $this->data['invoice_id'])
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Previous status') . ': ' . ucfirst($this->data['old_status']))
                    ->line(translate('New status') . ': ' . ucfirst($this->data['new_status']))
                    ->when(!empty($this->data['notes']), function ($message) {
                        return $message->line(translate('Notes') . ': ' . $this->data['notes']);
                    })
                    ->action(translate('View Invoice'), $this->data['invoice_url'])
                    ->line(translate('Thank you for using our platform.'));

            case self::TYPE_PAYMENT_CONFIRMED:
                return $mailMessage
                    ->subject(translate('Payment Confirmed'))
                    ->line(translate('Your payment has been confirmed!'))
                    ->line(translate('Invoice ID') . ': ' . $this->data['invoice_id'])
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Amount paid') . ': ' . $this->formatCurrency($this->data['amount']))
                    ->line(translate('Payment method') . ': ' . $this->data['payment_method'])
                    ->line(translate('Transaction ID') . ': ' . $this->data['transaction_id'])
                    ->action(translate('View Invoice'), $this->data['invoice_url'])
                    ->line(translate('Thank you for your payment!'));

            case self::TYPE_INVOICE_OVERDUE:
                return $mailMessage
                    ->subject(translate('Invoice Overdue - Action Required'))
                    ->line(translate('Your invoice is now overdue and requires immediate attention.'))
                    ->line(translate('Invoice ID') . ': ' . $this->data['invoice_id'])
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Amount due') . ': ' . $this->formatCurrency($this->data['amount']))
                    ->line(translate('Due date') . ': ' . $this->data['due_date'])
                    ->line(translate('Days overdue') . ': ' . $this->data['days_overdue'])
                    ->action(translate('Pay Now'), $this->data['invoice_url'])
                    ->line(translate('Please complete payment to avoid additional fees.'));

            case self::TYPE_INVOICE_CANCELLED:
                return $mailMessage
                    ->subject(translate('Invoice Cancelled'))
                    ->line(translate('Your invoice has been cancelled.'))
                    ->line(translate('Invoice ID') . ': ' . $this->data['invoice_id'])
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->when(!empty($this->data['reason']), function ($message) {
                        return $message->line(translate('Reason') . ': ' . $this->data['reason']);
                    })
                    ->action(translate('View Details'), $this->data['invoice_url'])
                    ->line(translate('If you have any questions, please contact support.'));

            case self::TYPE_PAYMENT_REMINDER:
                return $mailMessage
                    ->subject(translate('Payment Reminder - Action Required'))
                    ->line(translate('This is a reminder that your invoice payment is overdue.'))
                    ->line(translate('Invoice ID') . ': ' . $this->data['invoice_id'])
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Amount due') . ': ' . $this->formatCurrency($this->data['amount']))
                    ->line(translate('Due date') . ': ' . $this->data['due_date'])
                    ->line(translate('Days overdue') . ': ' . $this->data['days_overdue'])
                    ->when(!empty($this->data['custom_message']), function ($message) {
                        return $message->line(translate('Message') . ': ' . $this->data['custom_message']);
                    })
                    ->action(translate('Pay Now'), $this->data['invoice_url'])
                    ->line(translate('Please complete payment immediately to avoid additional fees.'));

            case self::TYPE_INSURANCE_DEPOSIT_PAID:
                return $mailMessage
                    ->subject(translate('Insurance Deposit Payment Confirmed'))
                    ->line(translate('Your insurance deposit payment has been confirmed!'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Deposit amount') . ': ' . $this->formatCurrency($this->data['amount']))
                    ->line(translate('Transaction ID') . ': ' . $this->data['transaction_id'])
                    ->action(translate('View Auction'), $this->data['auction_url'])
                    ->line(translate('You can now participate in the auction.'));

            case self::TYPE_INSURANCE_DEPOSIT_REFUND_REQUEST:
                return $mailMessage
                    ->subject(translate('Insurance Deposit Refund Request'))
                    ->line(translate('A refund request has been submitted for your insurance deposit.'))
                    ->line(translate('Deposit amount') . ': ' . $this->formatCurrency($this->data['amount']))
                    ->line(translate('Reason') . ': ' . ($this->data['reason'] ?? translate('Not specified')))
                    ->when(!empty($this->data['expected_refund_date']), function ($message) {
                        return $message->line(translate('Expected refund date') . ': ' . $this->data['expected_refund_date']);
                    })
                    ->action(translate('View Details'), $this->data['url'])
                    ->line(translate('Your refund will be processed within the specified timeframe.'));

            default:
                return $mailMessage
                    ->subject(translate('Auction Notification'))
                    ->line(translate('You have a new auction notification.'))
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
     * @return array
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
     * Get the web push notification title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        switch ($this->type) {
            case self::TYPE_OUTBID:
                return translate('You Have Been Outbid');
            case self::TYPE_OFFER_RECEIVED:
                return translate('New Offer Received');
            case self::TYPE_OFFER_ACCEPTED:
                return translate('Offer Accepted');
            case self::TYPE_OFFER_REJECTED:
                return translate('Offer Rejected');
            case self::TYPE_INVOICE_GENERATED:
                return translate('Invoice Generated');
            case self::TYPE_AUCTION_STARTING_SOON:
                return translate('Auction Starting Soon');
            case self::TYPE_ITEM_SOLD:
                return $this->data['is_winner'] ?? false ? translate('You Won!') : translate('Item Sold');
            case self::TYPE_INVOICE_STATUS_CHANGED:
                return translate('Invoice Status Updated');
            case self::TYPE_PAYMENT_CONFIRMED:
                return translate('Payment Confirmed');
            case self::TYPE_INVOICE_OVERDUE:
                return translate('Invoice Overdue');
            case self::TYPE_INVOICE_CANCELLED:
                return translate('Invoice Cancelled');
            case self::TYPE_PAYMENT_REMINDER:
                return translate('Payment Reminder');
            case self::TYPE_INSURANCE_DEPOSIT_PAID:
                return translate('Insurance Deposit Paid');
            case self::TYPE_INSURANCE_DEPOSIT_REFUND_REQUEST:
                return translate('Deposit Refund Request');
            default:
                return translate('Auction Notification');
        }
    }

    /**
     * Get the web push notification body.
     *
     * @return string
     */
    public function getBody(): string
    {
        switch ($this->type) {
            case self::TYPE_OUTBID:
                return translate('You have been outbid on') . ' ' . $this->data['car_name'] . '. ' . translate('Current bid') . ': ' . $this->formatCurrency($this->data['current_price']);
            case self::TYPE_OFFER_RECEIVED:
                return translate('New offer of') . ' ' . $this->formatCurrency($this->data['offer_amount']) . ' ' . translate('on') . ' ' . $this->data['car_name'];
            case self::TYPE_OFFER_ACCEPTED:
                return translate('Your offer of') . ' ' . $this->formatCurrency($this->data['offer_amount']) . ' ' . translate('has been accepted!');
            case self::TYPE_OFFER_REJECTED:
                return translate('Your offer on') . ' ' . $this->data['car_name'] . ' ' . translate('has been rejected.');
            case self::TYPE_INVOICE_GENERATED:
                return translate('Invoice for') . ' ' . $this->data['car_name'] . ': ' . $this->formatCurrency($this->data['amount']);
            case self::TYPE_AUCTION_STARTING_SOON:
                return translate('Auction for') . ' ' . $this->data['car_name'] . ' ' . translate('starts at') . ' ' . $this->data['start_time'];
            case self::TYPE_ITEM_SOLD:
                if ($this->data['is_winner'] ?? false) {
                    return translate('Congratulations! You won') . ' ' . $this->data['car_name'] . ' ' . translate('for') . ' ' . $this->formatCurrency($this->data['final_price']);
                } else {
                    return translate('Your') . ' ' . $this->data['car_name'] . ' ' . translate('sold for') . ' ' . $this->formatCurrency($this->data['final_price']);
                }
            case self::TYPE_INVOICE_STATUS_CHANGED:
                return translate('Invoice') . ' #' . $this->data['invoice_id'] . ' ' . translate('status changed to') . ' ' . $this->data['new_status'];
            case self::TYPE_PAYMENT_CONFIRMED:
                return translate('Payment of') . ' ' . $this->formatCurrency($this->data['amount']) . ' ' . translate('confirmed for') . ' ' . $this->data['car_name'];
            case self::TYPE_INVOICE_OVERDUE:
                return translate('Invoice') . ' #' . $this->data['invoice_id'] . ' ' . translate('is overdue. Amount') . ': ' . $this->formatCurrency($this->data['amount']);
            case self::TYPE_INVOICE_CANCELLED:
                return translate('Invoice') . ' #' . $this->data['invoice_id'] . ' ' . translate('has been cancelled');
            case self::TYPE_PAYMENT_REMINDER:
                return translate('Payment reminder: Invoice') . ' #' . $this->data['invoice_id'] . ' ' . translate('is') . ' ' . $this->data['days_overdue'] . ' ' . translate('days overdue');
            case self::TYPE_INSURANCE_DEPOSIT_PAID:
                return translate('Insurance deposit of') . ' ' . $this->formatCurrency($this->data['amount']) . ' ' . translate('confirmed for') . ' ' . $this->data['car_name'];
            case self::TYPE_INSURANCE_DEPOSIT_REFUND_REQUEST:
                return translate('Refund request submitted for insurance deposit of') . ' ' . $this->formatCurrency($this->data['amount']);
            default:
                return translate('You have a new auction notification.');
        }
    }

    /**
     * Get the web push notification URL.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->data['url'] ?? $this->data['auction_url'] ?? $this->data['invoice_url'] ?? $this->data['offer_url'] ?? url('/');
    }

    /**
     * Get SMS message for this notification.
     *
     * @return string
     */
    public function getSmsMessage(): string
    {
        switch ($this->type) {
            case self::TYPE_OUTBID:
                return translate('You have been outbid on') . ' ' . $this->data['car_name'] . '. ' . translate('Current bid') . ': ' . $this->formatCurrency($this->data['current_price']) . '. ' . translate('Place a higher bid now!');
            case self::TYPE_OFFER_ACCEPTED:
                return translate('Your offer of') . ' ' . $this->formatCurrency($this->data['offer_amount']) . ' ' . translate('on') . ' ' . $this->data['car_name'] . ' ' . translate('has been accepted! Please complete payment.');
            case self::TYPE_ITEM_SOLD:
                if ($this->data['is_winner'] ?? false) {
                    return translate('Congratulations! You won') . ' ' . $this->data['car_name'] . ' ' . translate('for') . ' ' . $this->formatCurrency($this->data['final_price']) . '. ' . translate('Please complete payment.');
                } else {
                    return translate('Your') . ' ' . $this->data['car_name'] . ' ' . translate('has been sold for') . ' ' . $this->formatCurrency($this->data['final_price']) . '.';
                }
            case self::TYPE_PAYMENT_CONFIRMED:
                return translate('Payment confirmed!') . ' ' . $this->formatCurrency($this->data['amount']) . ' ' . translate('received for') . ' ' . $this->data['car_name'] . '.';
            case self::TYPE_INVOICE_OVERDUE:
                return translate('URGENT: Invoice') . ' #' . $this->data['invoice_id'] . ' ' . translate('is overdue. Amount') . ': ' . $this->formatCurrency($this->data['amount']) . '. ' . translate('Please pay immediately.');
            case self::TYPE_PAYMENT_REMINDER:
                return translate('REMINDER: Invoice') . ' #' . $this->data['invoice_id'] . ' ' . translate('is') . ' ' . $this->data['days_overdue'] . ' ' . translate('days overdue. Amount') . ': ' . $this->formatCurrency($this->data['amount']) . '. ' . translate('Please pay immediately.');
            case self::TYPE_INSURANCE_DEPOSIT_PAID:
                return translate('Insurance deposit confirmed!') . ' ' . $this->formatCurrency($this->data['amount']) . ' ' . translate('received for') . ' ' . $this->data['car_name'] . '. ' . translate('You can now participate in the auction.');
            case self::TYPE_INSURANCE_DEPOSIT_REFUND_REQUEST:
                return translate('Insurance deposit refund request submitted for') . ' ' . $this->data['car_name'] . '. ' . translate('Amount') . ': ' . $this->formatCurrency($this->data['amount']) . '.';
            default:
                return translate('You have a new auction notification. Please check your account.');
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
