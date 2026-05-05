<?php

namespace App\Events;

use App\Models\AuctionItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemUnsold implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $broadcastQueue = 'auctions';
    public $itemId;
    public $roomId;
    public $car;
    public $startingPrice;
    public $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(AuctionItem $item)
    {
        $this->itemId = $item->id;
        $this->roomId = $item->auction_room_id;
        $this->car = [
            'id' => $item->car->id,
            'name' => $item->car->car_name ?? '',
            'thumbnail' => $item->car->main_photo_url ?? '',
        ];
        $this->startingPrice = $item->starting_price;
        $this->reason = 'no_bids';
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('auction-room.' . $this->roomId),
            new Channel('auction-item.' . $this->itemId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ItemUnsold';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'item_id' => $this->itemId,
            'car' => $this->car,
            'starting_price' => $this->startingPrice,
            'reason' => $this->reason,
        ];
    }
}
