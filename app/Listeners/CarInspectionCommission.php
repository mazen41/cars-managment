<?php

namespace App\Listeners;

use App\Events\CarInspectionPaid;
use App\Events\CarInspectorAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CarInspectionCommission
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
    public function handle(CarInspectionPaid | CarInspectorAssigned  $event): void
    {
        $inspection = $event->carInspection;
         if($inspection->inspector_id === null) {
            return; // No inspector assigned, so no commission to calculate
        }

        $inspection_commission = get_setting('inspection_commission', 0);
        $inspection_commission_type = get_setting('inspection_commission_type', 'percentage');

        $inspection_price = $inspection->inspectionType->price;

        if ($inspection_commission_type === 'flat') {
            $commission = $inspection_commission;
        } else {
            $commission = ($inspection_commission / 100) * $inspection_price;
        }
        $admin_to_pay = $inspection_price - $commission;

        $inspection->inspector->incrementOwedAmount($admin_to_pay);
        // Record the commission
        $inspection->commission()->create([
            'admin_commission' => $commission,
            'ownable_earning' => $admin_to_pay,
            'ownable_type' => get_class($inspection->inspector),
            'ownable_id' => $inspection->inspector->id,
        ]);
    }
}
