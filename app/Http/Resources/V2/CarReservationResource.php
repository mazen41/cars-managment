<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CarReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"    => $this->id,
            "reservation_amount"=> $this->payment->amount ?? convert_price(get_setting('car_reservation_amount')),
            "status"            => $this->status,
            "payment_method"    => $this->payment->method ?? '',
            "payment_status"    => $this->payment->status ?? '',
            "reserved_at"       => $this->reserved_at ? Carbon::parse($this->reserved_at)->format("d M Y, H:i") : null,
            'cancelled_at'      => $this->cancelled_at ? Carbon::parse($this->cancelled_at)->format("d M Y, H:i") : null,
            'cancellation_reason'=> $this->cancellation_reason,
            "car"  => $this->whenLoaded('car', function(){
                return new CarResource($this->car);
            })
        ];
    }
}
