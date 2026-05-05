<?php

namespace App\Events;

use App\Models\Bid;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidRejected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $broadcastQueue = 'auctions';
    public $itemId;
    public $bidId;
    public $amount;
    public $reason;
    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(Bid $bid)
    {
        $this->itemId = $bid->auction_item_id;
        $this->bidId = $bid->id;
        $this->amount = $bid->amount;
        $this->reason = $bid->rejection_reason;
        $this->userId = $bid->bidder_id;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'BidRejected';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'item_id' => $this->itemId,
            'bid_id' => $this->bidId,
            'amount' => $this->amount,
            'reason' => $this->reason,
        ];
    }
}
