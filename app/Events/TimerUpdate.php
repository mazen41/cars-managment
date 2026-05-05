<?php

namespace App\Events;

use App\Models\AuctionItem;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimerUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $itemId;
    public $endsAt;
    public $secondsRemaining;

    public $broadcastQueue = 'auction-timing';
    /**
     * Create a new event instance.
     */
    public function __construct(AuctionItem $item)
    {
        $this->itemId = $item->id;
        $this->endsAt = $item->ends_at;
        $this->secondsRemaining = Carbon::parse($item->ends_at)->diffInSeconds(Carbon::now(), true);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('auction-item.' . $this->itemId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'TimerUpdate';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'item_id' => $this->itemId,
            'ends_at' => $this->endsAt,
            'seconds_remaining' => (int) $this->secondsRemaining,
        ];
    }
}
