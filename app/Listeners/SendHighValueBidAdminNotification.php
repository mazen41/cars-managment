<?php

namespace App\Listeners;

use App\Events\BidAccepted;
use App\Models\User;
use App\Notifications\AdminAuctionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendHighValueBidAdminNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(BidAccepted $event): void
    {
        $bid = $event->bid;
        $auctionItem = $event->item;

        // Define high value threshold (configurable via settings)
        $highValueThreshold = get_setting('high_value_bid_threshold', 100000);

        // Only notify for high-value bids
        if ($bid->amount < $highValueThreshold) {
            return;
        }

        // Get admin and staff users for notification
        $notifiables = User::where('user_type', 'admin')
            ->orWhere(function($q) {
                $q->where('user_type', 'staff')
                  ->permission('view_auction_rooms');
            })
            ->get();

        if ($notifiables->isEmpty()) {
            return;
        }

        // Get previous bid amount
        $previousBid = $auctionItem->bids()
            ->where('id', '!=', $bid->id)
            ->orderBy('amount', 'desc')
            ->first();

        $previousAmount = $previousBid ? $previousBid->amount : $auctionItem->starting_price;

        // Prepare notification data
        $notificationData = [
            'car_name' => $auctionItem->car->car_name ?? 'Unknown Car',
            'bidder_name' => $bid->bidder->name ?? 'Unknown Bidder',
            'bid_amount' => $bid->amount,
            'previous_bid' => $previousAmount,
            'bid_increase' => $bid->amount - $previousAmount,
            'admin_url' => route('admin.auction-rooms.show', $event->roomId),
        ];

        // Send notification to all admin users
        foreach ($notifiables as $notifiable) {
            $notifiable->notify(new AdminAuctionNotification(
                AdminAuctionNotification::TYPE_HIGH_VALUE_BID,
                $notificationData
            ));
        }
    }
}
