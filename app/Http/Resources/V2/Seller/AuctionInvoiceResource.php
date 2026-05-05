<?php

namespace App\Http\Resources\V2\Seller;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionInvoiceResource extends JsonResource
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
            'amount' => single_price($this->amount),
            'auction_item_id'   => $this->auction_item_id,
            'commission_amount' => single_price($this->commission_amount),
            'net_amount' => single_price($this->net_amount),
            'status' => $this->status,
            'status_string' => translate($this->status),
            'due_date' => $this->due_date ? $this->due_date->format('Y-m-d H:i:s') : null,
            'paid_at' => $this->paid_at ? $this->paid_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            'auction_item' => $this->when($request->routeIs('api.v2.seller.auction-invoices.show'), function () {
                return [
                    'id' => $this->auctionItem->id,
                    'starting_price' => single_price($this->auctionItem->starting_price),
                    'current_price' => single_price($this->auctionItem->current_price),
                    'status' => $this->auctionItem->status,
                    'total_bids' => $this->auctionItem->total_bids,
                    'finalized_at' => $this->auctionItem->finalized_at,
                    'auction_room' => [
                        'id' => $this->auctionItem->auctionRoom->id,
                        'name' => $this->auctionItem->auctionRoom->name,
                        'commission_percentage' => $this->auctionItem->auctionRoom->commission_percentage
                    ],
                    'winner' => $this->auctionItem->currentWinner ? [
                        'id' => $this->auctionItem->currentWinner->id,
                        'name' => $this->auctionItem->currentWinner->name,
                        'phone' => $this->auctionItem->currentWinner->email
                    ] : null
                ];
            }),
            'car_info' => [
                'id' => $this->auctionItem->car->id,
                'name' => $this->auctionItem->car->car_name,
                'main_photo' => $this->auctionItem->car->main_photo_url
            ],
            'payment' =>  $this->when($request->routeIs('api.v2.seller.auction-invoices.show'), function () {
                return $this->payment ? [
                    'id' => $this->payment->id,
                    'amount' => single_price($this->payment->amount),
                    'status' => $this->payment->status,
                    'method' => $this->payment->method,
                    'transaction_id' => $this->payment->transaction_id,
                    'created_at' => $this->payment->created_at
                ] : null;
            })
        ];
    }
}
