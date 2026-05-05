<?php

namespace App\Http\Resources\V2\Auction;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V2\CarResource;

class AuctionItemResource extends JsonResource
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
            "current_winner_name"=> 'Bidder ' . substr(md5($this->current_winner_id), 0, 6),
            "can_receive_bids"  => $this->canReceiveBids(),
            "minimum_bid"   => $this->getMinimumBid(),
            "seconds_remaining" => $this->getSecondsRemaining(),
            "car" => CarResource::make($this->whenLoaded('car')),
        ];
    }
}
