<?php

namespace App\Listeners;

use App\Events\AuctionInvoicePaid;
use App\Models\User;
use App\Notifications\AdminAuctionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAuctionInvoicePaidAdminNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(AuctionInvoicePaid $event): void
    {
        $invoice = $event->invoice;

        // Get admin and staff users for notification
        $notifiables = User::where('user_type', 'admin')
            ->orWhere(function($q) {
                $q->where('user_type', 'staff')
                 ->permission('view_auction_invoices');
            })
            ->get();

        if ($notifiables->isEmpty()) {
            return; // No notifiable users found
        }

        // Prepare notification data
        $notificationData = [
            'invoice_id' => $invoice->id,
            'car_name' => $invoice->auctionItem->car->car_name ?? 'Unknown Car',
            'buyer_name' => $invoice->user->name ?? 'Unknown Buyer',
            'amount' => $invoice->amount,
            'payment_method' => $invoice->payment->payment_method ?? 'Unknown',
            'transaction_id' => $invoice->payment->transaction_id ?? 'N/A',
            'admin_url' => route('admin.auction-invoices.show', $invoice->id),
        ];

        // Send notification to all admin users
        foreach ($notifiables as $notifiable) {
            $notifiable->notify(new AdminAuctionNotification(
                AdminAuctionNotification::TYPE_AUCTION_INVOICE_PAID,
                $notificationData
            ));
        }
    }
}
