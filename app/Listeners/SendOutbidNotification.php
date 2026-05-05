<?php

namespace App\Listeners;

use App\Events\BidAccepted;
use App\Notifications\AuctionNotification;
use App\Jobs\SendSmsToUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOutbidNotification implements ShouldQueue
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
     * @param BidAccepted $event
     * @return void
     */
    public function handle(BidAccepted $event): void
    {
        try {
            $item = $event->item;
            $newBid = $event->bid;

            // Get the previous winner (who was just outbid)
            $previousWinnerId = $item->getOriginal('current_winner_id');

            if (!$previousWinnerId || $previousWinnerId == $newBid->bidder_id) {
                // No previous winner or same bidder bid again
                return;
            }

            $previousWinner = \App\Models\User::find($previousWinnerId);

            if (!$previousWinner) {
                return;
            }

            // Get the previous bid amount
            $previousBid = \App\Models\Bid::where('auction_item_id', $item->id)
                ->where('bidder_id', $previousWinnerId)
                ->where('status', 'outbid')
                ->orderBy('created_at', 'desc')
                ->first();

            $car = $item->car;

            $notificationData = [
                'car_name' => $car->name ?? 'Car #' . $car->id,
                'current_price' => $item->current_price,
                'your_bid' => $previousBid ? $previousBid->amount : 0,
                'auction_url' => url('/auction-items/' . $item->id),
                'url' => url('/auction-items/' . $item->id),
                'currency' => $item->auctionRoom->currency->code ?? currency_symbol(),
            ];

            // Send notification
            $notification = new AuctionNotification(
                AuctionNotification::TYPE_OUTBID,
                $notificationData
            );

            $previousWinner->notify($notification);

            // Send SMS if user has phone
            if ($previousWinner->phone) {
                SendSmsToUser::dispatch(
                    $previousWinner->id,
                    $notification->getSmsMessage(),
                    null
                );
            }

        } catch (\Exception $e) {
            Log::error('Failed to send outbid notification: ' . $e->getMessage(), [
                'item_id' => $event->item->id ?? null,
                'bid_id' => $event->bid->id ?? null,
            ]);
        }
    }
}
