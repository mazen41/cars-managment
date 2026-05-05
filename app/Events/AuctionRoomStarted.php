<?php

namespace App\Events;

use App\Models\AuctionRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionRoomStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $broadcastQueue = 'auctions';
    public $roomId;
    public $roomName;
    public $firstItemId;
    public $startedAt;

    /**
     * Create a new event instance.
     */
    public function __construct(AuctionRoom $room, $firstItemId)
    {
        $this->roomId = $room->id;
        $this->roomName = $room->name;
        $this->firstItemId = $firstItemId;
        $this->startedAt = $room->started_at;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('auction-room.' . $this->roomId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'AuctionRoomStarted';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->roomId,
            'room_name' => $this->roomName,
            'first_item_id' => $this->firstItemId,
            'started_at' => $this->startedAt,
        ];
    }
}
