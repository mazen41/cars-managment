<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\AuctionInvoicePaid;

class AuctionInvoiceCommission
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AuctionInvoicePaid $event): void
    {
        $invoice = $event->invoice;
        $item = $invoice->auctionItem;
        $sellerPayout = $item->sellerPayout;

        // Check if there is a seller payout associated otherwise it's a platform fee only invoice
        if($sellerPayout){
            $commission_amount = $sellerPayout->commission_amount;
            $net_amount = $invoice->net_amount;

            // Increment owed amount to seller
            $item->seller->shop->incrementOwedAmount($net_amount);

            // Record the commission
            $invoice->commission()->create([
                'admin_commission' => $commission_amount,
                'ownable_earning' => $net_amount,
                'ownable_type' => get_class($item->seller->shop),
                'ownable_id' => $item->seller->shop->id,
            ]);
        }
    }
}
