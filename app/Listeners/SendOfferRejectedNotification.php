<?php

namespace App\Listeners;

use App\Events\OfferRejected;
use App\Notifications\AuctionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOfferRejectedNotification implements ShouldQueue
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
     *
     * @param OfferRejected $event
     * @return void
     */
    public function handle(OfferRejected $event): void
    {
        try {
            $offer = $event->offer;
            $buyer = $offer->buyer;
            $item = $offer->auctionItem;
            $car = $item->car;

            $notificationData = [
                'car_name' => $car->name ?? 'Car #' . $car->id,
                'offer_amount' => $offer->amount,
                'reason' => $offer->seller_response,
                'auction_url' => url('/auction-items/' . $item->id),
                'url' => url('/auction-items/' . $item->id),
                'currency' => $item->auctionRoom->currency->code ?? currency_symbol(),
            ];

            // Send notification to buyer
            $notification = new AuctionNotification(
                AuctionNotification::TYPE_OFFER_REJECTED,
                $notificationData
            );

            $buyer->notify($notification);

            // Note: SMS not sent for offer rejected as it's not in the critical list

        } catch (\Exception $e) {
            Log::error('Failed to send offer rejected notification: ' . $e->getMessage(), [
                'offer_id' => $event->offer->id ?? null,
            ]);
        }
    }
}
