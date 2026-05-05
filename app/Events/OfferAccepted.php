<?php

namespace App\Events;

use App\Models\AuctionOffer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferAccepted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $offer;
    public $itemId;
    public $amount;
    public $acceptedAt;
    public $buyerId;
    public $sellerId;

    /**
     * Create a new event instance.
     */
    public function __construct(AuctionOffer $offer)
    {
        $this->offer = $offer;
        $this->itemId = $offer->auction_item_id;
        $this->amount = $offer->amount;
        $this->acceptedAt = $offer->responded_at;
        $this->buyerId = $offer->buyer_id;
        $this->sellerId = $offer->seller_id;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->buyerId),
            new PrivateChannel('seller.' . $this->sellerId),
            new Channel('auction-item.' . $this->itemId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'OfferAccepted';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'offer_id' => $this->offer->id,
            'item_id' => $this->itemId,
            'amount' => $this->amount,
            'accepted_at' => $this->acceptedAt,
        ];
    }
}
