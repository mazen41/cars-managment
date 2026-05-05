<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Car;

class CarModerationStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $car;
    public $oldModerationStatus;
    public $newModerationStatus;
    public $notes;

    /**
     * Create a new event instance.
     */
    public function __construct(Car $car, string $oldModerationStatus, string $newModerationStatus, ?string $notes = null)
    {
        $this->car = $car;
        $this->oldModerationStatus = $oldModerationStatus;
        $this->newModerationStatus = $newModerationStatus;
        $this->notes = $notes;
    }
}
