<?php

namespace App\Listeners;

use App\Events\AuctionInsuranceDepositPaid;
use App\Notifications\AuctionNotification;
use App\Jobs\SendSmsToUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInsuranceDepositPaidNotification implements ShouldQueue
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
    public function handle(AuctionInsuranceDepositPaid $event): void
    {
        $deposit = $event->deposit;
        $user = $deposit->user;
        $payment = $deposit->payment;

        // Prepare notification data
        $notificationData = [
            'car_name' => translate('Insurance Deposit'), // Generic name since deposit might not be tied to specific car
            'amount' => $deposit->amount,
            'currency' => get_setting('system_default_currency'),
            'transaction_id' => $payment ? $payment->transaction_id : $deposit->payment_id,
            'payment_method' => $payment ? $payment->payment_method : translate('Unknown'),
            'deposit_id' => $deposit->id,
            'paid_at' => $deposit->paid_at->format('Y-m-d H:i:s'),
            'url' => url('/customer/insurance-deposits/' . $deposit->id), // Fallback URL structure
        ];

        // Send notification
        $notification = new AuctionNotification(
            AuctionNotification::TYPE_INSURANCE_DEPOSIT_PAID,
            $notificationData
        );

        $user->notify($notification);

        // Send SMS if user has phone number
        if ($user->phone) {
            SendSmsToUser::dispatch($user->id, $notification->getSmsMessage(), null);
        }
    }
}
