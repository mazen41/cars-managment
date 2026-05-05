<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\CarReservation;

class CarReservationPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $carReservation;
    /**
     * Create a new event instance.
     */
    public function __construct(CarReservation $carReservation)
    {
        $this->carReservation = $carReservation;
    }

}
