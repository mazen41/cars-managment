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

class AdminAuctionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $type;
    public $data;
    public $className;

    // Admin notification types
    const TYPE_AUCTION_INVOICE_PAID = 'auction_invoice_paid_admin';
    const TYPE_HIGH_VALUE_BID = 'high_value_bid_admin';
    const TYPE_AUCTION_COMPLETED = 'auction_completed_admin';
    const TYPE_PAYMENT_FAILED = 'payment_failed_admin';
    const TYPE_INSURANCE_DEPOSIT_PAID = 'insurance_deposit_paid_admin';
    const TYPE_INSURANCE_DEPOSIT_REFUND = 'insurance_deposit_refund_admin';
    const TYPE_OFFER_SUBMITTED = 'offer_submitted_admin';
    const TYPE_DISPUTE_CREATED = 'dispute_created_admin';

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
        $this->className = AdminAuctionNotification::class;
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
            'mail',
            WebPushChannel::class,
            FcmChannel::class
        ];
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
            case self::TYPE_AUCTION_INVOICE_PAID:
                return $mailMessage
                    ->subject(translate('Auction Payment Received'))
                    ->line(translate('A payment has been received for an auction invoice.'))
                    ->line(translate('Invoice ID') . ': #' . $this->data['invoice_id'])
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Buyer') . ': ' . $this->data['buyer_name'])
                    ->line(translate('Amount') . ': ' . $this->formatCurrency($this->data['amount']))
                    ->line(translate('Payment method') . ': ' . $this->data['payment_method'])
                    ->line(translate('Transaction ID') . ': ' . $this->data['transaction_id'])
                    ->action(translate('View Invoice'), $this->data['admin_url'])
                    ->line(translate('Commission and payout processing will begin automatically.'));

            case self::TYPE_HIGH_VALUE_BID:
                return $mailMessage
                    ->subject(translate('High Value Bid Alert'))
                    ->line(translate('A high-value bid has been placed that requires attention.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Bidder') . ': ' . $this->data['bidder_name'])
                    ->line(translate('Bid amount') . ': ' . $this->formatCurrency($this->data['bid_amount']))
                    ->line(translate('Previous bid') . ': ' . $this->formatCurrency($this->data['previous_bid']))
                    ->line(translate('Increase') . ': ' . $this->formatCurrency($this->data['bid_increase']))
                    ->action(translate('View Auction'), $this->data['admin_url'])
                    ->line(translate('Please monitor this auction for any suspicious activity.'));

            case self::TYPE_AUCTION_COMPLETED:
                return $mailMessage
                    ->subject(translate('Auction Completed'))
                    ->line(translate('An auction has been completed and requires processing.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Seller') . ': ' . $this->data['seller_name'])
                    ->line(translate('Winner') . ': ' . ($this->data['winner_name'] ?? translate('No winner')))
                    ->line(translate('Final price') . ': ' . $this->formatCurrency($this->data['final_price']))
                    ->line(translate('Total bids') . ': ' . $this->data['total_bids'])
                    ->action(translate('Process Auction'), $this->data['admin_url'])
                    ->line(translate('Invoice generation and notifications will be processed automatically.'));

            case self::TYPE_PAYMENT_FAILED:
                return $mailMessage
                    ->subject(translate('Payment Failed - Action Required'))
                    ->line(translate('A payment attempt has failed and requires attention.'))
                    ->line(translate('Invoice ID') . ': #' . $this->data['invoice_id'])
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Buyer') . ': ' . $this->data['buyer_name'])
                    ->line(translate('Amount') . ': ' . $this->formatCurrency($this->data['amount']))
                    ->line(translate('Failure reason') . ': ' . $this->data['failure_reason'])
                    ->line(translate('Attempt number') . ': ' . $this->data['attempt_number'])
                    ->action(translate('Review Payment'), $this->data['admin_url'])
                    ->line(translate('Please contact the buyer or take appropriate action.'));

            case self::TYPE_INSURANCE_DEPOSIT_PAID:
                return $mailMessage
                    ->subject(translate('Insurance Deposit Received'))
                    ->line(translate('An insurance deposit has been paid for auction participation.'))
                    ->line(translate('Bidder') . ': ' . $this->data['bidder_name'])
                    ->line(translate('Deposit amount') . ': ' . $this->formatCurrency($this->data['amount']))
                    ->line(translate('Transaction ID') . ': ' . $this->data['transaction_id'])
                    ->action(translate('View Auction'), $this->data['admin_url'])
                    ->line(translate('The bidder is now eligible to participate in the auction.'));

            case self::TYPE_INSURANCE_DEPOSIT_REFUND:
                return $mailMessage
                    ->subject(translate('Insurance Deposit Refund Request'))
                    ->line(translate('A refund request has been submitted for an insurance deposit.'))
                    ->line(translate('Bidder') . ': ' . $this->data['bidder_name'])
                    ->line(translate('Deposit amount') . ': ' . $this->formatCurrency($this->data['amount']))
                    ->line(translate('Reason') . ': ' . ($this->data['reason'] ?? translate('Not specified')))
                    ->action(translate('Process Refund'), $this->data['admin_url'])
                    ->line(translate('Please review and process this refund request.'));

            case self::TYPE_OFFER_SUBMITTED:
                return $mailMessage
                    ->subject(translate('New Offer Submitted'))
                    ->line(translate('A new offer has been submitted for review.'))
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Seller') . ': ' . $this->data['seller_name'])
                    ->line(translate('Buyer') . ': ' . $this->data['buyer_name'])
                    ->line(translate('Offer amount') . ': ' . $this->formatCurrency($this->data['offer_amount']))
                    ->line(translate('Message') . ': ' . ($this->data['message'] ?? translate('No message')))
                    ->action(translate('View Offer'), $this->data['admin_url'])
                    ->line(translate('Monitor the seller\'s response to this offer.'));

            case self::TYPE_DISPUTE_CREATED:
                return $mailMessage
                    ->subject(translate('Dispute Created - Urgent Action Required'))
                    ->line(translate('A dispute has been created and requires immediate attention.'))
                    ->line(translate('Dispute ID') . ': #' . $this->data['dispute_id'])
                    ->line(translate('Car') . ': ' . $this->data['car_name'])
                    ->line(translate('Complainant') . ': ' . $this->data['complainant_name'])
                    ->line(translate('Respondent') . ': ' . $this->data['respondent_name'])
                    ->line(translate('Dispute type') . ': ' . $this->data['dispute_type'])
                    ->line(translate('Description') . ': ' . $this->data['description'])
                    ->action(translate('Handle Dispute'), $this->data['admin_url'])
                    ->line(translate('Please review and resolve this dispute promptly.'));

            default:
                return $mailMessage
                    ->subject(translate('Auction System Notification'))
                    ->line(translate('You have a new auction system notification.'))
                    ->action(translate('View Details'), $this->data['admin_url'] ?? url('/admin'));
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
            ->data($this->data);
    }

    /**
     * Get the web push notification title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        switch ($this->type) {
            case self::TYPE_AUCTION_INVOICE_PAID:
                return translate('Payment Received');
            case self::TYPE_HIGH_VALUE_BID:
                return translate('High Value Bid Alert');
            case self::TYPE_AUCTION_COMPLETED:
                return translate('Auction Completed');
            case self::TYPE_PAYMENT_FAILED:
                return translate('Payment Failed');
            case self::TYPE_INSURANCE_DEPOSIT_PAID:
                return translate('Insurance Deposit Received');
            case self::TYPE_INSURANCE_DEPOSIT_REFUND:
                return translate('Refund Request');
            case self::TYPE_OFFER_SUBMITTED:
                return translate('New Offer');
            case self::TYPE_DISPUTE_CREATED:
                return translate('Dispute Created');
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
            case self::TYPE_AUCTION_INVOICE_PAID:
                return translate('Payment of') . ' ' . $this->formatCurrency($this->data['amount']) . ' ' . translate('received for') . ' ' . $this->data['car_name'];
            case self::TYPE_HIGH_VALUE_BID:
                return translate('High bid of') . ' ' . $this->formatCurrency($this->data['bid_amount']) . ' ' . translate('on') . ' ' . $this->data['car_name'];
            case self::TYPE_AUCTION_COMPLETED:
                return $this->data['car_name'] . ' ' . translate('sold for') . ' ' . $this->formatCurrency($this->data['final_price']);
            case self::TYPE_PAYMENT_FAILED:
                return translate('Payment failed for invoice') . ' #' . $this->data['invoice_id'] . ' - ' . $this->data['car_name'];
            case self::TYPE_INSURANCE_DEPOSIT_PAID:
                return translate('Insurance deposit of') . ' ' . $this->formatCurrency($this->data['amount']) . ' ' . translate('received');
            case self::TYPE_INSURANCE_DEPOSIT_REFUND:
                return translate('Refund request for') . ' ' . $this->formatCurrency($this->data['amount']);
            case self::TYPE_OFFER_SUBMITTED:
                return translate('New offer of') . ' ' . $this->formatCurrency($this->data['offer_amount']) . ' ' . translate('on') . ' ' . $this->data['car_name'];
            case self::TYPE_DISPUTE_CREATED:
                return translate('New dispute created for') . ' ' . $this->data['car_name'] . ' - ' . translate('requires attention');
            default:
                return translate('You have a new auction system notification.');
        }
    }

    /**
     * Get the web push notification URL.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->data['admin_url'] ?? url('/admin');
    }

    /**
     * Format currency value.
     *
     * @param float $amount
     * @return string
     */
    protected function formatCurrency(float $amount): string
    {
        return single_price($amount);
    }
}
