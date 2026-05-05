<?php

namespace App\Listeners;

use App\Events\AuctionInsuranceDepositRefundRequested;
use App\Notifications\AuctionNotification;
use App\Jobs\SendSmsToUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInsuranceDepositRefundRequestNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AuctionInsuranceDepositRefundRequested $event): void
    {
        $deposit = $event->deposit;
        $user = $deposit->user;
        $reason = $event->reason;

        // Calculate expected refund date (e.g., 5-7 business days)
        $expectedRefundDate = now()->addDays(7)->format('Y-m-d');

        // Prepare notification data
        $notificationData = [
            'amount' => $deposit->amount,
            'currency' => get_setting('system_default_currency'),
            'deposit_id' => $deposit->id,
            'reason' => $reason,
            'expected_refund_date' => $expectedRefundDate,
            'url' => url('/customer/insurance-deposits/' . $deposit->id), // Fallback URL structure
        ];

        // Send notification
        $notification = new AuctionNotification(
            AuctionNotification::TYPE_INSURANCE_DEPOSIT_REFUND_REQUEST,
            $notificationData
        );

        $user->notify($notification);

        // Send SMS if user has phone number
        if ($user->phone) {
            SendSmsToUser::dispatch($user->id, $notification->getSmsMessage(), null);
        }
    }
}
