<?php

namespace App\Http\Resources\V2\Auction;

use App\Http\Resources\V2\Auction\AuctionItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => format_price($this->amount),
            'status' => $this->status,
            'invoice_type' => $this->invoice_type,
            'created_at' => $this->created_at,
            'due_date' => $this->due_date,
            'is_paid' => $this->isPaid(),
            'notes' => $this->notes,
            'paid_at' => $this->paid_at,
            'is_overdue' => $this->due_date && $this->due_date->isPast() && !$this->isPaid(),
            'auction_item' => AuctionItemResource::make($this->whenLoaded('auctionItem')),
        ];
    }
}
