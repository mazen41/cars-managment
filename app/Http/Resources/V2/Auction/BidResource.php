<?php

namespace App\Http\Resources\V2\Auction;

use Illuminate\Http\Resources\Json\JsonResource;

class BidResource extends JsonResource
{
    /**
     * * Transform the resource into an array.
     * * @param  \Illuminate\Http\Request  $request
     */

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'auction_item_id' => $this->auction_item_id,
            'amount' => $this->amount,
            'status' => $this->status,
            "item_status"   =>$this->auctionItem->status,
            "is_outbid"     => $this->isOutbid(),
            "is_accepted"    => $this->isAccepted(),
            "item_current_price" => $this->auctionItem->current_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'auction_item' => $this->when($this->relationLoaded('auctionItem') && $request->routeIs('api.v2.customer.bids.show'), new \App\Http\Resources\V2\Auction\AuctionItemResource($this->auctionItem)),
        ];
    }
}
