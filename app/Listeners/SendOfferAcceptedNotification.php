<?php

namespace App\Listeners;

use App\Events\OfferAccepted;
use App\Notifications\AuctionNotification;
use App\Jobs\SendSmsToUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOfferAcceptedNotification implements ShouldQueue
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
     * @param OfferAccepted $event
     * @return void
     */
    public function handle(OfferAccepted $event): void
    {
        try {
            $offer = $event->offer;
            $buyer = $offer->buyer;
            $item = $offer->auctionItem;
            $car = $item->car;

            // Get the buyer invoice
            $invoice = \App\Models\AuctionInvoice::where('auction_item_id', $item->id)
                ->where('invoice_type', 'buyer_payment')
                ->where('user_id', $buyer->id)
                ->first();

            $notificationData = [
                'car_name' => $car->car_name ?? 'Car #' . $car->id,
                'offer_amount' => $offer->amount,
                'invoice_url' => $invoice ? url('/api/v2/buyer/auction-invoices/' . $invoice->id) : url('/api/v2/buyer/my-offers'),
                'url' => $invoice ? url('/api/v2/buyer/auction-invoices/' . $invoice->id) : url('/api/v2/buyer/my-offers'),
                'currency' => $item->auctionRoom->currency->code ?? currency_symbol(),
            ];

            // Send notification to buyer
            $notification = new AuctionNotification(
                AuctionNotification::TYPE_OFFER_ACCEPTED,
                $notificationData
            );

            $buyer->notify($notification);

            // Send SMS if user has phone
            if ($buyer->phone) {
                SendSmsToUser::dispatch(
                    $buyer->id,
                    $notification->getSmsMessage(),
                    null
                );
            }

        } catch (\Exception $e) {
            Log::error('Failed to send offer accepted notification: ' . $e->getMessage(), [
                'offer_id' => $event->offer->id ?? null,
            ]);
        }
    }
}
