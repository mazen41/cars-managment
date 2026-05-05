<?php

namespace App\Events;

use App\Models\Bid;
use App\Models\AuctionItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidAccepted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $broadcastQueue = 'auctions';
    public $item;
    public $roomId;
    public $bid;
    public $bidderName;
    public $bidderId;
    public $amount;
    public $currentPrice;
    public $endsAt;
    public $totalBids;
    public $nextMinimumBid;
    public $timeExtendedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Bid $bid, AuctionItem $item, int $timeExtendedBy, int $nextMinimumBid)
    {
        $this->item = $item;
        $this->roomId = $item->auction_room_id;
        $this->bid = $bid;
        // Anonymize bidder name (e.g., "Bidder #123")
        $this->bidderName = $bid->bidder->name;
        $this->bidderId = $bid->bidder_id;
        $this->amount = $bid->amount;
        $this->currentPrice = $item->current_price;
        $this->endsAt = $item->ends_at;
        $this->totalBids = $item->total_bids;
        $this->timeExtendedBy = $timeExtendedBy;
        $this->nextMinimumBid = $nextMinimumBid;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('auction-room.' . $this->roomId),
            new Channel('auction-item.' . $this->item->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'BidAccepted';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'item_id' => $this->item->id,
            'bid_id' => $this->bid->id,
            'bidder_name' => $this->bidderName,
            'bidder_id' => $this->bidderId,
            'amount' => $this->amount,
            'current_price' => $this->currentPrice,
            'ends_at' => $this->endsAt,
            'total_bids' => $this->totalBids,
            'time_extended_by' => $this->timeExtendedBy,
            'next_minimum_bid' => $this->nextMinimumBid,
        ];
    }
}
