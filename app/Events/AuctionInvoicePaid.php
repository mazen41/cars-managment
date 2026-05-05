<?php

namespace App\Events;

use App\Models\AuctionInvoice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionInvoicePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AuctionInvoice $invoice;

    /**
     * Create a new event instance.
     */
    public function __construct(AuctionInvoice $invoice)
    {
        $this->invoice = $invoice;
    }
}
