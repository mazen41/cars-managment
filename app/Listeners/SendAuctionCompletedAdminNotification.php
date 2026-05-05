<?php

namespace App\Listeners;

use App\Events\ItemSold;
use App\Models\User;
use App\Notifications\AdminAuctionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAuctionCompletedAdminNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ItemSold $event): void
    {
        $auctionItem = $event->item;

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

        // Get auction details
        $winningBid = $auctionItem->bids()->orderBy('amount', 'desc')->first();
        $totalBids = $auctionItem->bids()->count();

        // Prepare notification data
        $notificationData = [
            'car_name' => $auctionItem->car->car_name ?? 'Unknown Car',
            'seller_name' => $auctionItem->seller->shop->name ?? 'Unknown Seller',
            'winner_name' => $winningBid ? $winningBid->bidder->name : null,
            'final_price' => $winningBid ? $winningBid->amount : $auctionItem->starting_price,
            'total_bids' => $totalBids,
            'currency' => $auctionItem->currency ?? get_setting('system_default_currency'),
            'admin_url' => route('admin.auction-rooms.show', $auctionItem->auctionRoom->id),
        ];

        // Send notification to all admin users
        foreach ($notifiables as $notifiable) {
            $notifiable->notify(new AdminAuctionNotification(
                AdminAuctionNotification::TYPE_AUCTION_COMPLETED,
                $notificationData
            ));
        }
    }
}
