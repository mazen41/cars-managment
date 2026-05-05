<?php

namespace App\Listeners;

use App\Events\OfferReceived;
use App\Models\User;
use App\Notifications\AdminAuctionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOfferSubmittedAdminNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OfferReceived $event): void
    {
        $offer = $event->offer;

        // Get admin and staff users for notification
        $notifiables = User::where('user_type', 'admin')
            ->orWhere(function($q) {
                $q->where('user_type', 'staff')
                  ->permission('view_auction_offers');
            })
            ->get();

        if ($notifiables->isEmpty()) {
            return;
        }

        // Prepare notification data
        $notificationData = [
            'car_name' => $offer->auctionItem->car->car_name ?? 'Unknown Car',
            'seller_name' => $offer->auctionItem->seller->shop->name ?? 'Admin',
            'buyer_name' => $offer->buyer->name ?? 'Unknown Buyer',
            'offer_amount' => $offer->amount,
            'message' => $offer->message ?? 'No message',
            'admin_url' => route('admin.auction-offers.show', $offer->id),
        ];

        // Send notification to all admin users
        foreach ($notifiables as $notifiable) {
            $notifiable->notify(new AdminAuctionNotification(
                AdminAuctionNotification::TYPE_OFFER_SUBMITTED,
                $notificationData
            ));
        }
    }
}
