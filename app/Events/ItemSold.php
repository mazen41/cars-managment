<?php

namespace App\Events;

use App\Models\AuctionItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemSold implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $broadcastQueue = 'auctions';
    public $item;
    public $roomId;
    public $car;
    public $finalPrice;
    public $winnerName;
    public $winnerId;
    public $totalBids;
    public $soldAt;

    /**
     * Create a new event instance.
     */
    public function __construct(AuctionItem $item)
    {
        $this->item = $item;
        $this->roomId = $item->auction_room_id;
        $this->car = [
            'id' => $item->car->id,
            'name' => $item->car->car_name ?? '',
            'thumbnail' => $item->car->main_photo_url ?? '',
        ];
        $this->finalPrice = $item->current_price;
        // Anonymize winner name
        $this->winnerName = 'Bidder #' . $item->current_winner_id;
        $this->winnerId = $item->current_winner_id;
        $this->totalBids = $item->total_bids;
        $this->soldAt = $item->finalized_at;
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
        return 'ItemSold';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'item_id' => $this->item->id,
            'car' => $this->car,
            'final_price' => $this->finalPrice,
            'winner_name' => $this->winnerName,
            'winner_id' => $this->winnerId,
            'total_bids' => $this->totalBids,
            'sold_at' => $this->soldAt,
        ];
    }
}
