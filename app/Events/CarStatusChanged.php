<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Car;

class CarStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $car;
    public $oldStatus;
    public $newStatus;
    public $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(Car $car, string $oldStatus, string $newStatus, ?string $reason = null)
    {
        $this->car = $car;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->reason = $reason;
    }
}
