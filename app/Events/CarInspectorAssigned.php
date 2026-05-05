<?php

namespace App\Events;

use App\Models\CarInspection;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CarInspectorAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $carInspection;
    /**
     * Create a new event instance.
     */
    public function __construct(CarInspection $carInspection)
    {
        $this->carInspection = $carInspection;
    }

}
