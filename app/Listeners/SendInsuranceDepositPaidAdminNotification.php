<?php

namespace App\Listeners;

use App\Events\AuctionInsuranceDepositPaid;
use App\Models\User;
use App\Notifications\AdminAuctionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInsuranceDepositPaidAdminNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(AuctionInsuranceDepositPaid $event): void
    {
        $deposit = $event->deposit;

        // Get admin and staff users for notification
        $notifiables = User::where('user_type', 'admin')
            ->orWhere(function($q) {
                $q->where('user_type', 'staff')
                  ->permission('view_insurance_deposits');
            })
            ->get();

        if ($notifiables->isEmpty()) {
            return;
        }

        // Prepare notification data
        $notificationData = [
            'bidder_name' => $deposit->user->name ?? 'Unknown Bidder',
            'amount' => $deposit->amount,
            'transaction_id' => $deposit->payment->transaction_id ?? 'N/A',
            'admin_url' => route('insurance-deposits.show', $deposit->id),
        ];

        // Send notification to all admin users
        foreach ($notifiables as $notifiable) {
            $notifiable->notify(new AdminAuctionNotification(
                AdminAuctionNotification::TYPE_INSURANCE_DEPOSIT_PAID,
                $notificationData
            ));
        }
    }
}
