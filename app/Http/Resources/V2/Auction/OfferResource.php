<?php

namespace App\Http\Resources\V2\Auction;

use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{

    /**
     *
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
            'seller_response' => $this->seller_response,
            'can_be_withdrawn' => $this->canBeWithdrawn(),
            "item_status"   =>$this->auctionItem->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'auction_item'  => AuctionItemResource::make($this->whenLoaded('auctionItem')),
        ];
    }
}
