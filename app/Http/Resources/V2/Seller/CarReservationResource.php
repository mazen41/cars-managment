<?php

namespace App\Http\Resources\V2\Seller;

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
            "reservation_amount"=> $this->payment->amount ?? single_price(get_setting('car_reservation_amount')),
            "status"            => $this->status,
            "reserved_at"       => $this->reserved_at ? Carbon::parse($this->reserved_at)->format("d M Y, H:i") : null,
            'cancelled_at'      => $this->cancelled_at ? Carbon::parse($this->cancelled_at)->format("d M Y, H:i") : null,
            'cancellation_reason'=> $this->cancellation_reason,
            "can_be_confirmed"   => $this->can_be_confirmed,
            "can_be_cancelled"   => $this->can_be_cancelled,
            "can_be_marked_as_sold" => $this->can_be_marked_as_sold,
            "customer"          => [
                "id"    => $this->user->id,
                "name"  => $this->user->name,
                "avatar"=> uploaded_asset($this->user->avatar_original),
            ],
            "payment"        => $this->whenLoaded('payment', function (){
                return [
                    "id"    => $this->payment->id,
                    "amount"=> $this->payment->amount,
                    "status"=> translate(ucfirst(str_replace('-', ' ', $this->payment->status))),
                    "paid_at"=> $this->payment->paid_at ? Carbon::parse($this->payment->paid_at)->format("d M Y, H:i") : null,
                    "payment_method"=> $this->payment->method,
                ];
            }),
            "car" => [
                "id" => $this->car->id,
                "name" => $this->car->car_name,
                "vin" => $this->car->vin,
                "main_photo" => $this->car->main_photo_url
            ]
        ];
    }
}
