<?php

namespace App\Events;

use App\Models\AuctionItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionItemStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $broadcastQueue = 'auctions';
    public $item;
    public $room;
    public $car;
    public $startingPrice;
    public $currentPrice;
    public $endsAt;
    public $baseTimerSeconds;

    /**
     * Create a new event instance.
     */
    public function __construct(AuctionItem $item)
    {
        $this->item = $item;
        $this->room = $item->auctionRoom;
        $this->car = [
            'id' => $item->car->id,
            'name' => $item->car->car_name ?? '',
            'thumbnail' => $item->car->main_photo_url ?? '',
        ];
        $this->startingPrice = $item->starting_price;
        $this->currentPrice = $item->current_price ?? $item->starting_price;
        $this->endsAt = $item->ends_at;
        $this->baseTimerSeconds = $item->auctionRoom->base_timer_seconds;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('auction-room.' . $this->room->id),
            new Channel('auction-item.' . $this->item->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'AuctionItemStarted';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'item_id' => $this->item->id,
            'car' => $this->car,
            'starting_price' => $this->startingPrice,
            'current_price' => $this->currentPrice,
            'sequence_order' => $this->item->sequence_order,
            'ends_at' => $this->endsAt,
            'base_timer_seconds' => $this->baseTimerSeconds,
        ];
    }
}
