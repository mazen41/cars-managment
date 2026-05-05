<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CarDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $carData;
    public $reason;
    public $deletedBy;

    /**
     * Create a new event instance.
     *
     * @param array $carData Car data before deletion (id, name, seller info, etc.)
     * @param string|null $reason Reason for deletion
     * @param string|null $deletedBy Name of the person who deleted the car
     */
    public function __construct(array $carData, ?string $reason = null, ?string $deletedBy = null)
    {
        $this->carData = $carData;
        $this->reason = $reason;
        $this->deletedBy = $deletedBy;
    }
}
