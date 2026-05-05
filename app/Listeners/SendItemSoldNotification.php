<?php

namespace App\Listeners;

use App\Events\ItemSold;
use App\Notifications\AuctionNotification;
use App\Jobs\SendSmsToUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendItemSoldNotification implements ShouldQueue
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
     * @param ItemSold $event
     * @return void
     */
    public function handle(ItemSold $event): void
    {
        try {
            $item = $event->item;
            $car = $item->car;
            $winner = $item->currentWinner;
            $seller = $item->seller;

            // Get invoices
            $buyerInvoice = \App\Models\AuctionInvoice::where('auction_item_id', $item->id)
                ->where('invoice_type', 'buyer_payment')
                ->first();

            $sellerInvoice = \App\Models\AuctionInvoice::where('auction_item_id', $item->id)
                ->where('invoice_type', 'seller_payout')
                ->first();

            // Notify winner
            if ($winner) {
                $winnerNotificationData = [
                    'car_name' => $car->car_name ?? 'Car #' . $car->id,
                    'final_price' => $item->current_price,
                    'is_winner' => true,
                    'invoice_url' => $buyerInvoice ? url('/api/v2/buyer/auction-invoices/' . $buyerInvoice->id) : url('/api/v2/buyer/my-bids'),
                    'url' => $buyerInvoice ? url('/api/v2/buyer/auction-invoices/' . $buyerInvoice->id) : url('/api/v2/buyer/my-bids'),
                    'currency' => $item->auctionRoom->currency->code ?? currency_symbol(),
                ];

                $winnerNotification = new AuctionNotification(
                    AuctionNotification::TYPE_ITEM_SOLD,
                    $winnerNotificationData
                );

                $winner->notify($winnerNotification);

                // Send SMS to winner
                if ($winner->phone) {
                    SendSmsToUser::dispatch(
                        $winner->id,
                        $winnerNotification->getSmsMessage(),
                        null
                    );
                }
            }

            // Notify seller
            if ($seller) {
                $sellerNotificationData = [
                    'car_name' => $car->name ?? 'Car #' . $car->id,
                    'final_price' => $item->current_price,
                    'is_winner' => false,
                    'invoice_url' => $sellerInvoice ? url('/api/v2/seller/auction-invoices/' . $sellerInvoice->id) : url('/api/v2/seller/auction-items'),
                    'url' => $sellerInvoice ? url('/api/v2/seller/auction-invoices/' . $sellerInvoice->id) : url('/api/v2/seller/auction-items'),
                    'currency' => $item->auctionRoom->currency->code ?? currency_symbol(),
                ];

                $sellerNotification = new AuctionNotification(
                    AuctionNotification::TYPE_ITEM_SOLD,
                    $sellerNotificationData
                );

                $seller->notify($sellerNotification);

                // Send SMS to seller
                if ($seller->phone) {
                    SendSmsToUser::dispatch(
                        $seller->id,
                        $sellerNotification->getSmsMessage(),
                        null
                    );
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to send item sold notification: ' . $e->getMessage(), [
                'item_id' => $event->item->id ?? null,
            ]);
        }
    }
}
