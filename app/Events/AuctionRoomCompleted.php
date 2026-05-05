<?php

namespace App\Events;

use App\Models\AuctionRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionRoomCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $broadcastQueue = 'auctions';
    public $roomId;
    public $roomName;
    public $completedAt;
    public $statistics;

    /**
     * Create a new event instance.
     */
    public function __construct(AuctionRoom $room, array $statistics)
    {
        $this->roomId = $room->id;
        $this->roomName = $room->name;
        $this->completedAt = $room->completed_at;
        $this->statistics = $statistics;
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
        return 'AuctionRoomCompleted';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->roomId,
            'room_name' => $this->roomName,
            'completed_at' => $this->completedAt,
            'statistics' => $this->statistics,
        ];
    }
}
