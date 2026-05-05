<?php

namespace App\Http\Resources\V2\Seller;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class AuctionListingRequestResource extends JsonResource
{
     /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'requested_starting_price' => single_price($this->requested_starting_price),
            'status'    => $this->status,
            'status_string' => translate($this->status),
            'admin_notes'   => $this->admin_notes,
            'reviewed_by'   => $this->reviewer->name ?? '',
            'reviewed_at'   => $this->reviewed_at ? $this->reviewed_at->format('Y-m-d h:i') : null,
            'created_at'    => $this->created_at ? $this->created_at->format('Y-m-d h:i') : null,
            'car_info'           => [
                'id'=> $this->car->id,
                'name' => $this->car->car_name,
                'main_photo'    => $this->car->main_photo_url
            ],
        ];
    }
}
