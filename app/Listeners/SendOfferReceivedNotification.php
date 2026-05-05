<?php

namespace App\Listeners;

use App\Events\OfferReceived;
use App\Notifications\AuctionNotification;
use App\Jobs\SendSmsToUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOfferReceivedNotification implements ShouldQueue
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
     * @param OfferReceived $event
     * @return void
     */
    public function handle(OfferReceived $event): void
    {
        try {
            $offer = $event->offer;
            $seller = $offer->seller;
            $item = $offer->auctionItem;
            $car = $item->car;

            $notificationData = [
                'car_name' => $car->car_name ?? 'Car #' . $car->id,
                'offer_amount' => $offer->amount,
                'message' => $offer->message,
                'offer_url' => url('/api/v2/seller/auction-offers/' . $offer->id),
                'url' => url('/api/v2/seller/auction-offers/' . $offer->id),
                'currency' => $item->auctionRoom->currency->code ?? currency_symbol(),
            ];

            // Send notification to seller
            $notification = new AuctionNotification(
                AuctionNotification::TYPE_OFFER_RECEIVED,
                $notificationData
            );

            $seller->notify($notification);

            // Note: SMS not sent for offer received as it's not in the critical list

        } catch (\Exception $e) {
            Log::error('Failed to send offer received notification: ' . $e->getMessage(), [
                'offer_id' => $event->offer->id ?? null,
            ]);
        }
    }
}
