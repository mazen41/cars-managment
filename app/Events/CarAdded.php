<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Car;

class CarAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $car;

    /**
     * Create a new event instance.
     */
    public function __construct(Car $car)
    {
        $this->car = $car;
    }
}
