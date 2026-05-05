<?php

namespace App\Http\Resources\V2\Seller;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionItemResource extends JsonResource
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
            'sequence_order' => $this->sequence_order,
            'starting_price' => $this->starting_price,
            'current_price' => $this->current_price,
            'status' => $this->status,
            'status_string' => translate($this->status),
            'started_at' => $this->started_at ? $this->started_at->format('Y-m-d H:i:s') : null,
            'ends_at' => $this->ends_at,
            'finalized_at' => $this->finalized_at ? $this->finalized_at->format('Y-m-d H:i:s') : null,
            'total_bids' => $this->total_bids,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'car_info'  => [
                'id'=> $this->car->id,
                'name' => $this->car->car_name,
                'main_photo'    => $this->car->main_photo_url
            ],
            'auction_room' => [
                'id' => $this->auctionRoom->id,
                'name' => $this->auctionRoom->name,
                'commission_percentage' => $this->auctionRoom->commission_percentage,
                'scheduled_at' => $this->auctionRoom->scheduled_start_at->format('Y-m-d H:i:s'),
                'status' => $this->auctionRoom->status
            ],
            'current_winner' => $this->currentWinner ? [
                'name' => $this->currentWinner->name,
                'phone' => $this->currentWinner->phone
            ] : null
        ];
    }
}
