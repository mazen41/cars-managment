<?php

namespace   App\Http\Resources\V2\Seller;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionOfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'buyer' => [
                'name' => $this->buyer->name,
                'phone' => $this->buyer->phone
            ],
            'amount' => single_price($this->amount),
            'status' => $this->status,
            'status_string' => translate($this->status),
            'buyer_message' => $this->message,
            'seller_response' => $this->seller_response,
            'responded_at' => $this->responded_at ? $this->responded_at->format('Y-m-d H:i:s') : null,
            'expires_at' => $this->expires_at ? $this->expires_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'auction_item' => [
                'id' => $this->auctionItem->id,
                'starting_price' => $this->auctionItem->starting_price,
                'current_price' => $this->auctionItem->current_price,
                'status' => $this->auctionItem->status,
                'auction_room' => [
                    'id' => $this->auctionItem->auctionRoom->id,
                    'name' => $this->auctionItem->auctionRoom->name,
                    'scheduled_at' => $this->auctionItem->auctionRoom->scheduled_start_at ? $this->auctionItem->auctionRoom->scheduled_start_at->format('Y-m-d H:i:s') : null,
                ],
                'car_info' => [
                    'id' => $this->auctionItem->car->id,
                    'name' => $this->auctionItem->car->car_name,
                    'main_photo' => $this->auctionItem->car->main_photo_url
                ],
            ],
            'can_be_accepted' => $this->canBeAccepted()
        ];
    }
}
