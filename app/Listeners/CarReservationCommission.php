<?php

namespace App\Listeners;

use App\Events\CarReservationPaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CarReservationCommission
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CarReservationPaid $event): void
    {
        $reservation_commission = get_setting('reservation_commission', 0);
        $reservation_commission_type = get_setting('reservation_commission_type', 'percentage');
        $reservation = $event->carReservation;
        $reservation_price = get_setting('car_reservation_amount', 0);

        if ($reservation_commission_type === 'flat') {
            $commission = $reservation_commission;
        } else {
            $commission = ($reservation_commission / 100) * $reservation_price;
        }
        $admin_to_pay = $reservation_price - $commission;
        $owner = $reservation->car->user;
        if($owner->user_type == 'seller'){
            $owner->shop->incrementOwedAmount($admin_to_pay);

             // Record the commission
            $reservation->commission()->create([
                'admin_commission' => $commission,
                'ownable_earning' => $admin_to_pay,
                'ownable_type' => get_class($owner->shop),
                'ownable_id' => $owner->shop->id,
            ]);
        }


    }
}
