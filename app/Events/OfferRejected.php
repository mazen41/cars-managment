<?php

namespace App\Events;

use App\Models\AuctionOffer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferRejected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $offerId;
    public $itemId;
    public $reason;
    public $rejectedAt;
    public $buyerId;

    /**
     * Create a new event instance.
     */
    public function __construct(AuctionOffer $offer)
    {
        $this->offerId = $offer->id;
        $this->itemId = $offer->auction_item_id;
        $this->reason = $offer->seller_response;
        $this->rejectedAt = $offer->responded_at;
        $this->buyerId = $offer->buyer_id;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->buyerId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'OfferRejected';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'offer_id' => $this->offerId,
            'item_id' => $this->itemId,
            'reason' => $this->reason,
            'rejected_at' => $this->rejectedAt,
        ];
    }
}
