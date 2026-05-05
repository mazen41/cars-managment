<?php
namespace App\Http\Resources\V2\Auction;

use Illuminate\Http\Resources\Json\JsonResource;

class AuctionRoomResource extends JsonResource
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
            "started_at" => $this->started_at,
            "completed_at" => $this->completed_at,
            "is_active" => $this->is_active,
            "total_items" => $this->getTotalItems(),
            "completed_items" => $this->getCompletedItems(),
            "current_item" => AuctionItemResource::make($this->getCurrentItem()),
            "next_item" => AuctionItemResource::make($this->getNextItem()),
            "items" => AuctionItemListResource::collection($this->auctionItems),
            'created_at' => $this->created_at,
        ];
    }

    public function with($request)
    {
        return [
            'status' => 'success',
            'message' => 'Auction data retrieved successfully',
            'code' => 200,
        ];
    }
}
