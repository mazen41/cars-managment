<?php

namespace App\Http\Resources\V2\Auction;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V2\CarListResource;

class AuctionItemListResource extends JsonResource
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
            'auction_room_id' => $this->auction_room_id,
            "starting_price" => $this->starting_price,
            "reserve_price" => $this->reserve_price,
            "current_price" => $this->current_price,
            "status" => $this->status,
            "started_at" => $this->started_at,
            "ends_at" => $this->ends_at,
            "total_bids" => $this->total_bids,
            "current_winner_id" => $this->current_winner_id,
            "car" => CarListResource::make($this->whenLoaded('car')),
        ];
    }
}
