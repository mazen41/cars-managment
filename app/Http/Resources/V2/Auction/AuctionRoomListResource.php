<?php

namespace App\Http\Resources\V2\Auction;

use Illuminate\Http\Resources\Json\JsonResource;


class AuctionRoomListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            "bid_increment_type" => $this->bid_increment_type,
            "bid_increment_value" => $this->bid_increment_value,
            "base_timer_seconds" => $this->base_timer_seconds,
            //"insurance_deposit_amount" => $this->insurance_deposit_amount,
            "extension_seconds" => $this->extension_seconds,
            'scheduled_start_at' => $this->scheduled_start_at,
            "completed_at" => $this->completed_at,
            "total_items" => $this->getTotalItems(),
            "completed_items" => $this->getCompletedItems(),
            "current_item" => $this->getCurrentItem(),
            "is_active" => $this->isActive(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
