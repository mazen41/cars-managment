<?php

namespace App\Events;

use App\Models\UserInsuranceDeposit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionInsuranceDepositRefundRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public UserInsuranceDeposit $deposit;
    public ?string $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(UserInsuranceDeposit $deposit, ?string $reason = null)
    {
        $this->deposit = $deposit;
        $this->reason = $reason;
    }
}