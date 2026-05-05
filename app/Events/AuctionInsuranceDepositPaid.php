<?php

namespace App\Events;

use App\Models\UserInsuranceDeposit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionInsuranceDepositPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public UserInsuranceDeposit $deposit;

    /**
     * Create a new event instance.
     */
    public function __construct(UserInsuranceDeposit $deposit)
    {
        $this->deposit = $deposit;
    }
}
