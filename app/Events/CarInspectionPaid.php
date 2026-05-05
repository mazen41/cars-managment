<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\CarInspection;

class CarInspectionPaid
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
